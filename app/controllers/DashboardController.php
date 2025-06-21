<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/StatusChamado.php';

/**
 * Controlador para o dashboard
 */
class DashboardController extends Controller
{
    private $chamadoModel;
    private $setorModel;
    private $statusModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Se não estiver autenticado, redireciona para o login
        if (!is_authenticated()) {
            redirect('auth');
            exit;
        }

        // Inicializa os modelos
        $this->chamadoModel = new Chamado();
        $this->setorModel = new Setor();
        $this->statusModel = new StatusChamado();
    }

    /**
     * Página do dashboard
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // Obter estatísticas básicas
        $estatisticas = $this->obterEstatisticas($empresaId);

        // Obter dados para os gráficos
        $chamadosPorStatus = $this->obterChamadosPorStatus($empresaId);
        $chamadosPorSetor = $this->obterChamadosPorSetor($empresaId);
        $chamadosPorMes = $this->obterChamadosPorMes($empresaId);
        $tempoMedioPorSetor = $this->obterTempoMedioPorSetor($empresaId);
        $chamadosPorTipoServico = $this->obterChamadosPorTipoServico($empresaId);
        $chamadosPorDiaSemana = $this->obterChamadosPorDiaSemana($empresaId);

        // Obter chamados recentes
        $chamadosRecentes = $this->obterChamadosRecentes($empresaId);

        // Obtém a lista de setores
        try {
            $setores = $this->setorModel->findAll(
                'empresa_id = :empresa_id AND ativo = 1',
                ['empresa_id' => $empresaId],
                'nome ASC'
            );
        } catch (Exception $e) {
            $setores = [];
        }

        $this->render('dashboard/index', [
            'estatisticas' => $estatisticas,
            'setores' => $setores,
            'chamadosPorStatus' => $chamadosPorStatus,
            'chamadosPorSetor' => $chamadosPorSetor,
            'chamadosPorMes' => $chamadosPorMes,
            'tempoMedioPorSetor' => $tempoMedioPorSetor,
            'chamadosPorTipoServico' => $chamadosPorTipoServico,
            'chamadosPorDiaSemana' => $chamadosPorDiaSemana,
            'recentes' => $chamadosRecentes
        ]);
    }

    /**
     * Obtém estatísticas básicas
     */
    private function obterEstatisticas($empresaId)
    {
        try {
            // Total de chamados
            $total = $this->chamadoModel->count('empresa_id = :empresa_id', ['empresa_id' => $empresaId]);

            // Chamados por status
            $abertos = $this->chamadoModel->count(
                'empresa_id = :empresa_id AND status_id = 1',
                ['empresa_id' => $empresaId]
            );

            $emAndamento = $this->chamadoModel->count(
                'empresa_id = :empresa_id AND status_id = 2',
                ['empresa_id' => $empresaId]
            );

            $concluidos = $this->chamadoModel->count(
                'empresa_id = :empresa_id AND status_id = 4',
                ['empresa_id' => $empresaId]
            );

            // Chamados concluídos hoje
            $hoje = date('Y-m-d');
            $concluidosHoje = $this->chamadoModel->count(
                'empresa_id = :empresa_id AND status_id = 4 AND DATE(data_conclusao) = :hoje',
                ['empresa_id' => $empresaId, 'hoje' => $hoje]
            );

            // Tempo médio de atendimento
            $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_criacao, data_conclusao)) as tempo_medio 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND status_id = 4 
                    AND data_conclusao IS NOT NULL";
            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $tempoMedio = $result ? round($result['tempo_medio'] ?? 0, 1) : 0;

            return [
                'total' => $total,
                'abertos' => $abertos,
                'em_andamento' => $emAndamento,
                'concluidos' => $concluidos,
                'concluidos_hoje' => $concluidosHoje,
                'tempo_medio_atendimento' => $tempoMedio
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0,
                'concluidos_hoje' => 0,
                'tempo_medio_atendimento' => 0
            ];
        }
    }

    /**
     * Obtém chamados por status
     */
    private function obterChamadosPorStatus($empresaId)
    {
        try {
            // Buscar todos os status
            $statusList = $this->statusModel->findAll('', [], 'id ASC');

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($statusList as $status) {
                $labels[] = $status['nome'];

                // Contar chamados com este status
                $count = $this->chamadoModel->count(
                    'empresa_id = :empresa_id AND status_id = :status_id',
                    ['empresa_id' => $empresaId, 'status_id' => $status['id']]
                );

                $data[] = $count;
                $backgroundColor[] = $status['cor'] ?? '#' . substr(md5($status['nome']), 0, 6);
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status: ' . $e->getMessage());
            return ['labels' => [], 'data' => [], 'backgroundColor' => []];
        }
    }

    /**
     * Obtém chamados por setor
     */
    private function obterChamadosPorSetor($empresaId)
    {
        try {
            // Buscar setores da empresa
            $setores = $this->setorModel->findAll(
                'empresa_id = :empresa_id AND ativo = 1',
                ['empresa_id' => $empresaId],
                'nome ASC'
            );

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($setores as $setor) {
                $labels[] = $setor['nome'];

                // Contar chamados deste setor
                $count = $this->chamadoModel->count(
                    'empresa_id = :empresa_id AND setor_id = :setor_id',
                    ['empresa_id' => $empresaId, 'setor_id' => $setor['id']]
                );

                $data[] = $count;
                $backgroundColor[] = '#' . substr(md5($setor['nome']), 0, 6);
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por setor: ' . $e->getMessage());
            return ['labels' => [], 'data' => [], 'backgroundColor' => []];
        }
    }

    /**
     * Obtém chamados por mês
     */
    private function obterChamadosPorMes($empresaId)
    {
        try {
            $meses = [
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro'
            ];

            $data = array_fill(0, 12, 0);
            $ano = date('Y');

            // Buscar chamados por mês
            $sql = "SELECT MONTH(data_criacao) as mes, COUNT(*) as total 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id AND YEAR(data_criacao) = :ano 
                    GROUP BY MONTH(data_criacao)";

            $result = $this->chamadoModel->executeQuery($sql, [
                'empresa_id' => $empresaId,
                'ano' => $ano
            ]);

            foreach ($result as $row) {
                $mesIndex = (int)$row['mes'] - 1;
                $data[$mesIndex] = (int)$row['total'];
            }

            return [
                'labels' => $meses,
                'data' => $data
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    /**
     * Obtém tempo médio por setor
     */
    private function obterTempoMedioPorSetor($empresaId)
    {
        try {
            // Buscar setores da empresa
            $setores = $this->setorModel->findAll(
                'empresa_id = :empresa_id AND ativo = 1',
                ['empresa_id' => $empresaId],
                'nome ASC'
            );

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($setores as $setor) {
                // Calcular tempo médio de atendimento para este setor
                $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_criacao, data_conclusao)) as tempo_medio 
                        FROM chamados 
                        WHERE empresa_id = :empresa_id 
                        AND setor_id = :setor_id 
                        AND status_id = 4 
                        AND data_conclusao IS NOT NULL";

                $result = $this->chamadoModel->executeQuerySingle($sql, [
                    'empresa_id' => $empresaId,
                    'setor_id' => $setor['id']
                ]);

                $tempoMedio = $result ? round($result['tempo_medio'] ?? 0, 1) : 0;

                if ($tempoMedio > 0) {
                    $labels[] = $setor['nome'];
                    $data[] = $tempoMedio;
                    $backgroundColor[] = '#' . substr(md5($setor['nome']), 0, 6);
                }
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter tempo médio por setor: ' . $e->getMessage());
            return ['labels' => [], 'data' => [], 'backgroundColor' => []];
        }
    }

    /**
     * Obtém chamados por tipo de serviço
     */
    private function obterChamadosPorTipoServico($empresaId)
    {
        try {
            $sql = "SELECT tipo_servico, COUNT(*) as total 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND tipo_servico IS NOT NULL 
                    AND tipo_servico != '' 
                    GROUP BY tipo_servico 
                    ORDER BY total DESC 
                    LIMIT 5";

            $result = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($result as $row) {
                $labels[] = $row['tipo_servico'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = '#' . substr(md5($row['tipo_servico']), 0, 6);
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por tipo de serviço: ' . $e->getMessage());
            return ['labels' => [], 'data' => [], 'backgroundColor' => []];
        }
    }

    /**
     * Obtém chamados por dia da semana
     */
    private function obterChamadosPorDiaSemana($empresaId)
    {
        try {
            $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            $data = array_fill(0, 7, 0);
            $backgroundColor = [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40',
                '#C9CBCF'
            ];

            $sql = "SELECT DAYOFWEEK(data_criacao) as dia, COUNT(*) as total 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    GROUP BY DAYOFWEEK(data_criacao)";

            $result = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);

            foreach ($result as $row) {
                $diaIndex = (int)$row['dia'] - 1;
                $data[$diaIndex] = (int)$row['total'];
            }

            return [
                'labels' => $diasSemana,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por dia da semana: ' . $e->getMessage());
            return ['labels' => [], 'data' => [], 'backgroundColor' => []];
        }
    }

    /**
     * Obtém chamados recentes
     */
    private function obterChamadosRecentes($empresaId)
    {
        try {
            $chamados = $this->chamadoModel->findAll(
                'empresa_id = :empresa_id',
                ['empresa_id' => $empresaId],
                'data_criacao DESC',
                10
            );

            $chamadosFormatados = [];

            foreach ($chamados as $chamado) {
                // Buscar nome do status
                $status = $this->statusModel->findById($chamado['status_id'] ?? 1);

                // Buscar nome do setor
                $setor = null;
                if (!empty($chamado['setor_id'])) {
                    $setor = $this->setorModel->findById($chamado['setor_id']);
                }

                $chamadosFormatados[] = [
                    'id' => $chamado['id'],
                    'solicitante' => $chamado['titulo'] ?? 'Sem título',
                    'descricao' => $chamado['descricao'] ?? '',
                    'data_solicitacao' => $chamado['data_criacao'] ?? date('Y-m-d H:i:s'),
                    'setor' => $setor ? $setor['nome'] : 'Não definido',
                    'status' => $status ? $status['nome'] : 'Não definido',
                    'status_cor' => $status ? $status['cor'] : '#6c757d'
                ];
            }

            return $chamadosFormatados;
        } catch (Exception $e) {
            error_log('Erro ao obter chamados recentes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém dados para gráficos do dashboard via AJAX
     */
    public function getChartData()
    {
        // Verifica se é uma requisição AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso não permitido']);
            exit;
        }

        $empresaId = get_empresa_id();

        try {
            // Obtém estatísticas básicas
            $estatisticas = $this->obterEstatisticas($empresaId);

            // Obtém dados para os gráficos
            $chamadosPorStatus = $this->obterChamadosPorStatus($empresaId);
            $chamadosPorSetor = $this->obterChamadosPorSetor($empresaId);
            $chamadosPorMes = $this->obterChamadosPorMes($empresaId);
            $tempoMedioPorSetor = $this->obterTempoMedioPorSetor($empresaId);
            $chamadosPorTipoServico = $this->obterChamadosPorTipoServico($empresaId);
            $chamadosPorDiaSemana = $this->obterChamadosPorDiaSemana($empresaId);

            echo json_encode([
                'estatisticas' => $estatisticas,
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorMes' => $chamadosPorMes,
                'tempoMedioPorSetor' => $tempoMedioPorSetor,
                'chamadosPorTipoServico' => $chamadosPorTipoServico,
                'chamadosPorDiaSemana' => $chamadosPorDiaSemana
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao obter dados: ' . $e->getMessage()]);
        }
        exit;
    }
}
