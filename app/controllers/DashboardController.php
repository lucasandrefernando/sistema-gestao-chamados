<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/StatusChamado.php';

/**
 * Controlador para o dashboard
 * Responsável por gerenciar a exibição e atualização dos dados do dashboard
 */
class DashboardController extends Controller
{
    private $chamadoModel;
    private $setorModel;
    private $statusModel;

    /**
     * Construtor
     * Inicializa os modelos necessários e verifica autenticação
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
     * Página principal do dashboard
     * Carrega e exibe todos os dados e gráficos
     */
    public function index()
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Log para debug
        error_log("Dashboard - Empresa ID: $empresaId, Usuário ID: $usuarioId");

        // Verifica se o usuário é admin
        $isAdmin = is_admin();
        error_log("Usuário é admin: " . ($isAdmin ? 'Sim' : 'Não'));

        // Obtém os setores aos quais o usuário tem acesso diretamente da tabela usuarios_setores
        $setoresIds = $this->obterSetoresDoUsuario($usuarioId);
        error_log("Setores do usuário: " . json_encode($setoresIds));

        // Se o usuário for admin, ele tem acesso a todos os setores
        // Mas ainda assim, vamos respeitar os setores associados a ele na tabela usuarios_setores
        if ($isAdmin && empty($setoresIds)) {
            // Se for admin e não tiver setores específicos, obtém todos os setores da empresa
            $sql = "SELECT id FROM setores WHERE empresa_id = :empresa_id AND ativo = 1";
            $result = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
            $setoresIds = array_column($result, 'id');
            error_log("Admin sem setores específicos - obtendo todos os setores: " . json_encode($setoresIds));
        } else if (empty($setoresIds)) {
            // Se não for admin e não tiver setores, mostra mensagem
            error_log("Usuário não tem acesso a nenhum setor");
            set_flash_message('warning', 'Você não tem acesso a nenhum setor. Entre em contato com o administrador.');
            redirect('home');
            return;
        }

        // Obtém os detalhes dos setores
        $setores = [];
        if (!empty($setoresIds)) {
            $sql = "SELECT id, nome FROM setores 
                WHERE empresa_id = :empresa_id 
                AND id IN (" . implode(',', $setoresIds) . ")
                AND ativo = 1";
            $setores = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
        }

        // Inicializa as estatísticas gerais
        $estatisticasGerais = [
            'total' => 0,
            'abertos' => 0,
            'em_andamento' => 0,
            'concluidos' => 0,
            'concluidos_hoje' => 0,
            'tempo_medio_atendimento' => 0
        ];

        // Obtém estatísticas básicas
        try {
            // Consulta para total de chamados
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $estatisticasGerais['total'] = $result ? $result['total'] : 0;
            error_log("Total de chamados: " . $estatisticasGerais['total']);

            // Consulta para chamados abertos (status_id = 1)
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id AND status_id = 1";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $estatisticasGerais['abertos'] = $result ? $result['total'] : 0;
            error_log("Chamados abertos: " . $estatisticasGerais['abertos']);

            // Consulta para chamados em andamento (status_id = 2)
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id AND status_id = 2";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $estatisticasGerais['em_andamento'] = $result ? $result['total'] : 0;
            error_log("Chamados em andamento: " . $estatisticasGerais['em_andamento']);

            // Consulta para chamados concluídos (status_id = 4)
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id AND status_id = 4";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $estatisticasGerais['concluidos'] = $result ? $result['total'] : 0;
            error_log("Chamados concluídos: " . $estatisticasGerais['concluidos']);

            // Consulta para chamados concluídos hoje
            $hoje = date('Y-m-d');
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id 
                AND status_id = 4 
                AND DATE(data_conclusao) = :hoje";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $params = ['empresa_id' => $empresaId, 'hoje' => $hoje];
            $result = $this->chamadoModel->executeQuerySingle($sql, $params);
            $estatisticasGerais['concluidos_hoje'] = $result ? $result['total'] : 0;
            error_log("Chamados concluídos hoje: " . $estatisticasGerais['concluidos_hoje']);

            // Consulta para tempo médio de atendimento
            $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, data_conclusao)) as tempo_medio 
                FROM chamados 
                WHERE empresa_id = :empresa_id 
                AND status_id = 4 
                AND data_conclusao IS NOT NULL";

            // Adiciona filtro de setores
            if (!empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
            $estatisticasGerais['tempo_medio_atendimento'] = $result && $result['tempo_medio'] ? round($result['tempo_medio'], 1) : 0;
            error_log("Tempo médio de atendimento: " . $estatisticasGerais['tempo_medio_atendimento']);
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
        }

        // Obtém dados para os gráficos
        $chamadosPorStatus = $this->obterChamadosPorStatus($empresaId, $setoresIds, $isAdmin);
        $chamadosPorSetor = $this->obterChamadosPorSetor($empresaId, $setoresIds, $isAdmin);
        $chamadosPorMes = $this->obterChamadosPorMes($empresaId, $setoresIds, $isAdmin);
        $tempoMedioPorSetor = $this->obterTempoMedioPorSetor($empresaId, $setoresIds, $isAdmin);
        $chamadosPorTipoServico = $this->obterChamadosPorTipoServico($empresaId, $setoresIds, $isAdmin);
        $chamadosPorDiaSemana = $this->obterChamadosPorDiaSemana($empresaId, $setoresIds, $isAdmin);

        // Obtém chamados recentes
        $chamadosRecentes = $this->obterChamadosRecentes($empresaId, $setoresIds, $isAdmin);

        // Renderiza a view com todos os dados
        $this->render('dashboard/index', [
            'estatisticas' => $estatisticasGerais,
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
     * Obtém os IDs dos setores aos quais o usuário tem acesso
     * 
     * @param int $usuarioId ID do usuário
     * @return array Array com IDs dos setores
     */
    private function obterSetoresDoUsuario($usuarioId)
    {
        try {
            // Consulta direta na tabela usuarios_setores
            $sql = "SELECT setor_id FROM usuarios_setores WHERE usuario_id = :usuario_id";
            $result = $this->chamadoModel->executeQuery($sql, ['usuario_id' => $usuarioId]);

            // Extrai apenas os IDs dos setores
            $setoresIds = [];
            foreach ($result as $row) {
                $setoresIds[] = $row['setor_id'];
            }

            return $setoresIds;
        } catch (Exception $e) {
            error_log('Erro ao obter setores do usuário: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém a lista de status de chamados
     * 
     * @return array Lista de status
     */
    private function obterStatusChamados()
    {
        try {
            $sql = "SELECT id, nome, cor FROM status_chamados WHERE ativo = 1 ORDER BY id";
            $result = $this->chamadoModel->executeQuery($sql);

            if (empty($result)) {
                // Se não encontrar registros, retorna status padrão
                return [
                    ['id' => 1, 'nome' => 'Aberto', 'cor' => '#FFC107'],         // Amarelo
                    ['id' => 2, 'nome' => 'Em Atendimento', 'cor' => '#007BFF'], // Azul
                    ['id' => 3, 'nome' => 'Pausado', 'cor' => '#9C27B0'],        // Roxo
                    ['id' => 4, 'nome' => 'Concluído', 'cor' => '#28A745'],      // Verde
                    ['id' => 5, 'nome' => 'Cancelado', 'cor' => '#343A40']       // Preto
                ];
            }

            // Garante que as cores estejam padronizadas
            $coresStatus = [
                1 => '#FFC107', // Amarelo - Aberto
                2 => '#007BFF', // Azul - Em Atendimento
                3 => '#9C27B0', // Roxo - Pausado
                4 => '#28A745', // Verde - Concluído
                5 => '#343A40'  // Preto - Cancelado
            ];

            foreach ($result as &$status) {
                // Substitui a cor do banco de dados pela cor padronizada
                if (isset($coresStatus[$status['id']])) {
                    $status['cor'] = $coresStatus[$status['id']];
                }
            }

            return $result;
        } catch (Exception $e) {
            error_log('Erro ao obter status de chamados: ' . $e->getMessage());
            // Retorna status padrão em caso de erro
            return [
                ['id' => 1, 'nome' => 'Aberto', 'cor' => '#FFC107'],         // Amarelo
                ['id' => 2, 'nome' => 'Em Atendimento', 'cor' => '#007BFF'], // Azul
                ['id' => 3, 'nome' => 'Pausado', 'cor' => '#9C27B0'],        // Roxo
                ['id' => 4, 'nome' => 'Concluído', 'cor' => '#28A745'],      // Verde
                ['id' => 5, 'nome' => 'Cancelado', 'cor' => '#343A40']       // Preto
            ];
        }
    }

    /**
     * Obtém uma cor consistente para um setor com base no seu ID
     * 
     * @param int $setorId ID do setor
     * @return string Código de cor hexadecimal
     */
    private function obterCorSetor($setorId)
    {
        // Lista de cores predefinidas para setores
        $cores = [
            '#4285F4', // Azul Google
            '#EA4335', // Vermelho Google
            '#FBBC05', // Amarelo Google
            '#34A853', // Verde Google
            '#673AB7', // Roxo Material
            '#3F51B5', // Índigo Material
            '#2196F3', // Azul Material
            '#009688', // Verde-azulado Material
            '#4CAF50', // Verde Material
            '#8BC34A', // Verde-limão Material
            '#CDDC39', // Lima Material
            '#FFC107', // Âmbar Material
            '#FF9800', // Laranja Material
            '#FF5722', // Laranja-profundo Material
            '#795548', // Marrom Material
            '#9E9E9E', // Cinza Material
            '#607D8B'  // Azul-cinza Material
        ];

        // Usa o ID do setor para selecionar uma cor consistente
        $indice = ($setorId % count($cores));
        return $cores[$indice];
    }

    /**
     * Obtém dados para o gráfico de chamados por status
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterChamadosPorStatus($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Obtém a lista de status
            $statusList = $this->obterStatusChamados();

            // Inicializa arrays para armazenar os dados
            $labels = [];
            $data = [];
            $backgroundColor = [];

            // Cores padronizadas para os status
            $coresStatus = [
                1 => '#FFC107', // Amarelo - Aberto
                2 => '#007BFF', // Azul - Em Atendimento
                3 => '#9C27B0', // Roxo - Pausado
                4 => '#28A745', // Verde - Concluído
                5 => '#343A40'  // Preto - Cancelado
            ];

            foreach ($statusList as $status) {
                $labels[] = $status['nome'];

                // Consulta para contar chamados com este status
                $sql = "SELECT COUNT(*) as total 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND status_id = :status_id";

                $params = [
                    'empresa_id' => $empresaId,
                    'status_id' => $status['id']
                ];

                // Se não for admin e tiver setores específicos, adiciona filtro de setores
                if (!$isAdmin && !empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, $params);
                $data[] = $result ? (int)$result['total'] : 0;

                // Usa a cor padronizada
                $backgroundColor[] = isset($coresStatus[$status['id']]) ? $coresStatus[$status['id']] : '#6C757D';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => []
            ];
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por setor
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterChamadosPorSetor($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Obtém a lista de setores
            $setores = [];

            if ($isAdmin) {
                // Se for admin, obtém todos os setores
                $sql = "SELECT id, nome FROM setores WHERE empresa_id = :empresa_id AND ativo = 1";
                $setores = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
            } else if (!empty($setoresIds)) {
                // Se não for admin, obtém apenas os setores aos quais tem acesso
                $sql = "SELECT id, nome FROM setores 
                    WHERE empresa_id = :empresa_id 
                    AND ativo = 1 
                    AND id IN (" . implode(',', $setoresIds) . ")";
                $setores = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
            }

            // Inicializa arrays para armazenar os dados
            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($setores as $setor) {
                $labels[] = $setor['nome'];

                // Consulta para contar chamados deste setor
                $sql = "SELECT COUNT(*) as total 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND setor_id = :setor_id";

                $result = $this->chamadoModel->executeQuerySingle($sql, [
                    'empresa_id' => $empresaId,
                    'setor_id' => $setor['id']
                ]);

                $data[] = $result ? (int)$result['total'] : 0;

                // Usa a função para obter uma cor consistente para o setor
                $backgroundColor[] = $this->obterCorSetor($setor['id']);
            }

            // Se não houver dados, adiciona um placeholder
            if (empty($labels)) {
                $labels[] = 'Sem dados';
                $data[] = 0;
                $backgroundColor[] = '#C9CBCF';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por setor: ' . $e->getMessage());
            return [
                'labels' => ['Erro ao carregar dados'],
                'data' => [0],
                'backgroundColor' => ['#C9CBCF']
            ];
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por mês
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterChamadosPorMes($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Se for admin ou não tiver setores específicos, usa o método existente
            if ($isAdmin || empty($setoresIds)) {
                $chamadosPorMes = $this->chamadoModel->getChamadosPorMes($empresaId);
                return [
                    'labels' => $chamadosPorMes['labels'],
                    'data' => $chamadosPorMes['data'],
                    'backgroundColor' => '#4361ee'
                ];
            }

            // Inicializa arrays para armazenar os dados
            $mesesData = [];

            // Para cada setor, obtém os chamados por mês e agrega
            foreach ($setoresIds as $setorId) {
                $chamadosPorMes = $this->chamadoModel->getChamadosPorMesESetor($empresaId, $setorId);

                foreach ($chamadosPorMes as $mes) {
                    $mesAno = isset($mes['mes_ano']) ? $mes['mes_ano'] : '';
                    $total = isset($mes['total']) ? (int)$mes['total'] : 0;

                    // Extrai o mês do formato MM/YYYY
                    $partes = explode('/', $mesAno);
                    if (count($partes) == 2) {
                        $mesNum = (int)$partes[0];

                        if (!isset($mesesData[$mesNum])) {
                            $mesesData[$mesNum] = 0;
                        }

                        $mesesData[$mesNum] += $total;
                    }
                }
            }

            // Formata os dados para o gráfico
            $meses = [
                1 => 'Janeiro',
                2 => 'Fevereiro',
                3 => 'Março',
                4 => 'Abril',
                5 => 'Maio',
                6 => 'Junho',
                7 => 'Julho',
                8 => 'Agosto',
                9 => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro'
            ];

            $labels = array_values($meses);
            $data = array_fill(0, 12, 0);

            foreach ($mesesData as $mesNum => $total) {
                if ($mesNum >= 1 && $mesNum <= 12) {
                    $data[$mesNum - 1] = $total;
                }
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => '#4361ee'
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => '#4361ee'
            ];
        }
    }

    /**
     * Obtém dados para o gráfico de tempo médio por setor
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterTempoMedioPorSetor($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Obtém a lista de setores
            $setores = [];

            if ($isAdmin) {
                // Se for admin, obtém todos os setores
                $sql = "SELECT id, nome FROM setores WHERE empresa_id = :empresa_id AND ativo = 1";
                $setores = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
            } else if (!empty($setoresIds)) {
                // Se não for admin, obtém apenas os setores aos quais tem acesso
                $sql = "SELECT id, nome FROM setores 
                    WHERE empresa_id = :empresa_id 
                    AND ativo = 1 
                    AND id IN (" . implode(',', $setoresIds) . ")";
                $setores = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
            }

            // Inicializa arrays para armazenar os dados
            $labels = [];
            $data = [];
            $backgroundColor = [];

            $tempoMedioSetores = [];

            foreach ($setores as $setor) {
                // Consulta para calcular o tempo médio de atendimento
                $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, data_conclusao)) as tempo_medio 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND setor_id = :setor_id
                    AND status_id = 4
                    AND data_conclusao IS NOT NULL";

                $result = $this->chamadoModel->executeQuerySingle($sql, [
                    'empresa_id' => $empresaId,
                    'setor_id' => $setor['id']
                ]);

                $tempoMedio = $result && $result['tempo_medio'] ? round($result['tempo_medio'], 1) : 0;

                // Só adiciona setores com tempo médio maior que zero
                if ($tempoMedio > 0) {
                    $tempoMedioSetores[] = [
                        'id' => $setor['id'],
                        'nome' => $setor['nome'],
                        'tempo_medio' => $tempoMedio
                    ];
                }
            }

            // Ordena por tempo médio (do menor para o maior)
            usort($tempoMedioSetores, function ($a, $b) {
                return $a['tempo_medio'] - $b['tempo_medio'];
            });

            // Limita a 10 setores
            $tempoMedioSetores = array_slice($tempoMedioSetores, 0, 10);

            // Formata os dados para o gráfico
            foreach ($tempoMedioSetores as $setor) {
                $labels[] = $setor['nome'];
                $data[] = $setor['tempo_medio'];

                // Usa a função para obter uma cor consistente para o setor
                $backgroundColor[] = $this->obterCorSetor($setor['id']);
            }

            // Se não houver dados, adiciona um placeholder
            if (empty($labels)) {
                $labels[] = 'Sem dados';
                $data[] = 0;
                $backgroundColor[] = '#C9CBCF';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter tempo médio por setor: ' . $e->getMessage());
            return [
                'labels' => ['Erro ao carregar dados'],
                'data' => [0],
                'backgroundColor' => ['#C9CBCF']
            ];
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por tipo de serviço
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterChamadosPorTipoServico($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Consulta SQL para obter chamados por tipo de serviço
            $sql = "SELECT 
                    COALESCE(tipo_servico, 'Não definido') as tipo,
                    setor_id,
                    COUNT(*) as total
                FROM chamados
                WHERE empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            // Se não for admin e tiver setores específicos, adiciona filtro de setores
            if (!$isAdmin && !empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $sql .= " GROUP BY tipo, setor_id
                ORDER BY total DESC";

            $result = $this->chamadoModel->executeQuery($sql, $params);

            // Agrupa os resultados por tipo de serviço
            $tiposServico = [];
            foreach ($result as $row) {
                $tipo = $row['tipo'];
                $setorId = $row['setor_id'];
                $total = $row['total'];

                if (!isset($tiposServico[$tipo])) {
                    $tiposServico[$tipo] = [
                        'total' => 0,
                        'setores' => []
                    ];
                }

                $tiposServico[$tipo]['total'] += $total;
                $tiposServico[$tipo]['setores'][$setorId] = isset($tiposServico[$tipo]['setores'][$setorId])
                    ? $tiposServico[$tipo]['setores'][$setorId] + $total
                    : $total;
            }

            // Ordena os tipos de serviço por total
            uasort($tiposServico, function ($a, $b) {
                return $b['total'] - $a['total'];
            });

            // Limita a 10 tipos de serviço
            $tiposServico = array_slice($tiposServico, 0, 10, true);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($tiposServico as $tipo => $info) {
                $labels[] = $tipo;
                $data[] = $info['total'];

                // Encontra o setor mais comum para este tipo de serviço
                arsort($info['setores']);
                $setorMaisComum = key($info['setores']);

                // Usa a cor do setor mais comum
                $backgroundColor[] = $this->obterCorSetor($setorMaisComum);
            }

            // Se não houver dados, adiciona um placeholder
            if (empty($labels)) {
                $labels[] = 'Sem dados';
                $data[] = 0;
                $backgroundColor[] = '#C9CBCF';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por tipo de serviço: ' . $e->getMessage());
            return [
                'labels' => ['Erro ao carregar dados'],
                'data' => [0],
                'backgroundColor' => ['#C9CBCF']
            ];
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por dia da semana
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com dados para o gráfico
     */
    private function obterChamadosPorDiaSemana($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Mapeia os dias da semana
            $diasSemana = [
                1 => 'Domingo',
                2 => 'Segunda',
                3 => 'Terça',
                4 => 'Quarta',
                5 => 'Quinta',
                6 => 'Sexta',
                7 => 'Sábado'
            ];

            // Inicializa o array de dados com zeros
            $dadosDias = array_fill(1, 7, 0);

            // Consulta SQL para obter chamados por dia da semana
            $sql = "SELECT 
                    DAYOFWEEK(data_solicitacao) as dia_semana, 
                    COUNT(*) as total
                FROM chamados
                WHERE empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            // Se não for admin e tiver setores específicos, adiciona filtro de setores
            if (!$isAdmin && !empty($setoresIds)) {
                $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $sql .= " GROUP BY dia_semana
                ORDER BY dia_semana";

            $result = $this->chamadoModel->executeQuery($sql, $params);

            // Preenche o array de dados com os resultados da consulta
            foreach ($result as $row) {
                $diaSemana = (int)$row['dia_semana'];
                if ($diaSemana >= 1 && $diaSemana <= 7) {
                    $dadosDias[$diaSemana] = (int)$row['total'];
                }
            }

            // Formata os dados para o gráfico
            $labels = array_values($diasSemana);
            $data = array_values($dadosDias);

            // Cores para cada dia da semana
            $backgroundColor = [
                '#FF6384', // Domingo - Vermelho
                '#36A2EB', // Segunda - Azul
                '#FFCE56', // Terça - Amarelo
                '#4BC0C0', // Quarta - Verde água
                '#9966FF', // Quinta - Roxo
                '#FF9F40', // Sexta - Laranja
                '#C9CBCF'  // Sábado - Cinza
            ];

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por dia da semana: ' . $e->getMessage());
            return [
                'labels' => array_values($diasSemana),
                'data' => array_fill(0, 7, 0),
                'backgroundColor' => [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#C9CBCF'
                ]
            ];
        }
    }

    /**
     * Obtém chamados recentes
     * 
     * @param int $empresaId ID da empresa
     * @param array $setoresIds IDs dos setores aos quais o usuário tem acesso
     * @param bool $isAdmin Indica se o usuário é admin
     * @return array Array com chamados recentes formatados
     */
    private function obterChamadosRecentes($empresaId, $setoresIds, $isAdmin)
    {
        try {
            // Consulta SQL para obter chamados recentes
            $sql = "SELECT 
                    c.id, 
                    c.solicitante, 
                    c.descricao, 
                    c.data_solicitacao, 
                    c.status_id,
                    c.setor_id,
                    s.nome as setor_nome,
                    st.nome as status_nome
                FROM chamados c
                LEFT JOIN setores s ON c.setor_id = s.id
                LEFT JOIN status_chamados st ON c.status_id = st.id
                WHERE c.empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            // Se não for admin e tiver setores específicos, adiciona filtro de setores
            if (!$isAdmin && !empty($setoresIds)) {
                $sql .= " AND c.setor_id IN (" . implode(',', $setoresIds) . ")";
            }

            $sql .= " ORDER BY c.data_solicitacao DESC
                LIMIT 10";

            $result = $this->chamadoModel->executeQuery($sql, $params);

            // Formata os dados para o padrão esperado
            $chamadosRecentes = [];

            // Cores padronizadas para os status
            $coresStatus = [
                1 => '#FFC107', // Amarelo - Aberto
                2 => '#007BFF', // Azul - Em Atendimento
                3 => '#9C27B0', // Roxo - Pausado
                4 => '#28A745', // Verde - Concluído
                5 => '#343A40'  // Preto - Cancelado
            ];

            foreach ($result as $chamado) {
                // Formata a data para exibição
                $dataSolicitacao = new DateTime($chamado['data_solicitacao']);
                $dataFormatada = $dataSolicitacao->format('d/m/Y H:i');

                // Define a cor do status
                $statusId = $chamado['status_id'] ?? 0;
                $statusCor = isset($coresStatus[$statusId]) ? $coresStatus[$statusId] : '#6C757D';

                $chamadosRecentes[] = [
                    'id' => $chamado['id'],
                    'solicitante' => $chamado['solicitante'] ?? 'Não informado',
                    'descricao' => $chamado['descricao'] ?? 'Sem descrição',
                    'data_solicitacao' => $dataFormatada,
                    'setor_id' => $chamado['setor_id'] ?? 0,
                    'setor' => $chamado['setor_nome'] ?? 'Não definido',
                    'status' => $chamado['status_nome'] ?? 'Não definido',
                    'status_cor' => $statusCor
                ];
            }

            return $chamadosRecentes;
        } catch (Exception $e) {
            error_log('Erro ao obter chamados recentes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém o nome de um setor pelo ID
     * 
     * @param int $setorId ID do setor
     * @return string Nome do setor
     */
    private function getNomeSetor($setorId)
    {
        try {
            $setor = $this->setorModel->findById($setorId);
            return $setor ? $setor['nome'] : 'Não definido';
        } catch (Exception $e) {
            return 'Não definido';
        }
    }

    /**
     * Obtém a cor de um status pelo ID
     * 
     * @param int $statusId ID do status
     * @return string Cor do status
     */
    private function getCorStatus($statusId)
    {
        $cores = [
            1 => '#dc3545', // Aberto - Vermelho
            2 => '#ffc107', // Em Andamento - Amarelo
            3 => '#17a2b8', // Aguardando - Azul claro
            4 => '#28a745', // Concluído - Verde
            5 => '#6c757d'  // Cancelado - Cinza
        ];

        return $cores[$statusId] ?? '#6c757d';
    }

    /**
     * Obtém dados para o dashboard via AJAX
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
        $usuarioId = get_user_id();

        try {
            // Verifica se o usuário é admin
            $isAdmin = is_admin();

            // Obtém os setores aos quais o usuário tem acesso
            $setoresIds = $this->obterSetoresDoUsuario($usuarioId);

            // Se o usuário for admin e não tiver setores específicos, obtém todos os setores
            if ($isAdmin && empty($setoresIds)) {
                $sql = "SELECT id FROM setores WHERE empresa_id = :empresa_id AND ativo = 1";
                $result = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);
                $setoresIds = array_column($result, 'id');
            }

            // Inicializa as estatísticas gerais
            $estatisticasGerais = [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0,
                'concluidos_hoje' => 0,
                'tempo_medio_atendimento' => 0
            ];

            // Obtém estatísticas básicas
            try {
                // Consulta para total de chamados
                $sql = "SELECT COUNT(*) as total FROM chamados 
                    WHERE empresa_id = :empresa_id";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
                $estatisticasGerais['total'] = $result ? $result['total'] : 0;

                // Consulta para chamados abertos (status_id = 1)
                $sql = "SELECT COUNT(*) as total FROM chamados 
                    WHERE empresa_id = :empresa_id AND status_id = 1";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
                $estatisticasGerais['abertos'] = $result ? $result['total'] : 0;

                // Consulta para chamados em andamento (status_id = 2)
                $sql = "SELECT COUNT(*) as total FROM chamados 
                    WHERE empresa_id = :empresa_id AND status_id = 2";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
                $estatisticasGerais['em_andamento'] = $result ? $result['total'] : 0;

                // Consulta para chamados concluídos (status_id = 4)
                $sql = "SELECT COUNT(*) as total FROM chamados 
                    WHERE empresa_id = :empresa_id AND status_id = 4";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
                $estatisticasGerais['concluidos'] = $result ? $result['total'] : 0;

                // Consulta para chamados concluídos hoje
                $hoje = date('Y-m-d');
                $sql = "SELECT COUNT(*) as total FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND status_id = 4 
                    AND DATE(data_conclusao) = :hoje";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $params = ['empresa_id' => $empresaId, 'hoje' => $hoje];
                $result = $this->chamadoModel->executeQuerySingle($sql, $params);
                $estatisticasGerais['concluidos_hoje'] = $result ? $result['total'] : 0;

                // Consulta para tempo médio de atendimento
                $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, data_conclusao)) as tempo_medio 
                    FROM chamados 
                    WHERE empresa_id = :empresa_id 
                    AND status_id = 4 
                    AND data_conclusao IS NOT NULL";

                // Adiciona filtro de setores
                if (!empty($setoresIds)) {
                    $sql .= " AND setor_id IN (" . implode(',', $setoresIds) . ")";
                }

                $result = $this->chamadoModel->executeQuerySingle($sql, ['empresa_id' => $empresaId]);
                $estatisticasGerais['tempo_medio_atendimento'] = $result && $result['tempo_medio'] ? round($result['tempo_medio'], 1) : 0;
            } catch (Exception $e) {
                error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            }

            // Obtém dados para os gráficos
            $chamadosPorStatus = $this->obterChamadosPorStatus($empresaId, $setoresIds, $isAdmin);
            $chamadosPorSetor = $this->obterChamadosPorSetor($empresaId, $setoresIds, $isAdmin);
            $chamadosPorMes = $this->obterChamadosPorMes($empresaId, $setoresIds, $isAdmin);
            $tempoMedioPorSetor = $this->obterTempoMedioPorSetor($empresaId, $setoresIds, $isAdmin);
            $chamadosPorTipoServico = $this->obterChamadosPorTipoServico($empresaId, $setoresIds, $isAdmin);
            $chamadosPorDiaSemana = $this->obterChamadosPorDiaSemana($empresaId, $setoresIds, $isAdmin);

            // Obtém chamados recentes
            $chamadosRecentes = $this->obterChamadosRecentes($empresaId, $setoresIds, $isAdmin);

            echo json_encode([
                'estatisticas' => $estatisticasGerais,
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorMes' => $chamadosPorMes,
                'tempoMedioPorSetor' => $tempoMedioPorSetor,
                'chamadosPorTipoServico' => $chamadosPorTipoServico,
                'chamadosPorDiaSemana' => $chamadosPorDiaSemana,
                'recentes' => $chamadosRecentes
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao obter dados: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Mapeia tipos de serviço para setores
     * 
     * @param int $empresaId ID da empresa
     * @return array Mapeamento de tipos de serviço para setores
     */
    private function mapearTiposServicoParaSetores($empresaId)
    {
        try {
            // Obtém todos os chamados com tipo de serviço e setor
            $sql = "SELECT DISTINCT tipo_servico, setor_id 
                FROM chamados 
                WHERE empresa_id = :empresa_id 
                AND tipo_servico IS NOT NULL 
                AND tipo_servico != ''";

            $result = $this->chamadoModel->executeQuery($sql, ['empresa_id' => $empresaId]);

            // Cria um mapeamento de tipos de serviço para setores
            $mapeamento = [];

            foreach ($result as $row) {
                $tipoServico = $row['tipo_servico'];
                $setorId = $row['setor_id'];

                // Associa o tipo de serviço ao setor mais comum
                if (!isset($mapeamento[$tipoServico]) || $mapeamento[$tipoServico]['count'] < $row['count']) {
                    $mapeamento[$tipoServico] = $setorId;
                }
            }

            return $mapeamento;
        } catch (Exception $e) {
            error_log('Erro ao mapear tipos de serviço para setores: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método de diagnóstico para verificar dados
     */
    public function diagnostico()
    {
        if (!is_authenticated()) {
            echo "Não autenticado";
            exit;
        }

        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        echo "<h1>Diagnóstico do Dashboard</h1>";
        echo "<p>Empresa ID: $empresaId</p>";
        echo "<p>Usuário ID: $usuarioId</p>";

        try {
            // Verifica se o usuário é admin
            $isAdmin = is_admin();
            echo "<p>É admin: " . ($isAdmin ? 'Sim' : 'Não') . "</p>";

            // Verifica setores do usuário
            $setores = $this->setorModel->getSetoresByUsuario($usuarioId, $empresaId);
            $setoresIds = array_column($setores, 'id');

            echo "<h2>Setores do Usuário</h2>";
            echo "<p>Total de setores: " . count($setores) . "</p>";

            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Total Chamados</th></tr>";

            foreach ($setores as $setor) {
                $totalChamados = $this->setorModel->contarChamados($setor['id']);
                echo "<tr>";
                echo "<td>" . $setor['id'] . "</td>";
                echo "<td>" . $setor['nome'] . "</td>";
                echo "<td>" . $totalChamados . "</td>";
                echo "</tr>";
            }

            echo "</table>";

            // Verifica estatísticas básicas
            $estatisticasEmpresa = $this->chamadoModel->getEstatisticas($empresaId);

            echo "<h2>Estatísticas da Empresa</h2>";
            echo "<p>Total de chamados: " . $estatisticasEmpresa['total'] . "</p>";
            echo "<p>Chamados abertos: " . $estatisticasEmpresa['abertos'] . "</p>";
            echo "<p>Chamados em andamento: " . $estatisticasEmpresa['em_andamento'] . "</p>";
            echo "<p>Chamados concluídos: " . $estatisticasEmpresa['concluidos'] . "</p>";

            // Verifica chamados por status
            $chamadosPorStatus = $this->obterChamadosPorStatus($empresaId, $setoresIds, $isAdmin);

            echo "<h2>Chamados por Status</h2>";
            echo "<p>Labels: " . implode(', ', $chamadosPorStatus['labels']) . "</p>";
            echo "<p>Dados: " . implode(', ', $chamadosPorStatus['data']) . "</p>";

            // Verifica chamados por setor
            $chamadosPorSetor = $this->obterChamadosPorSetor($empresaId, $setoresIds, $isAdmin);

            echo "<h2>Chamados por Setor</h2>";
            echo "<p>Labels: " . implode(', ', $chamadosPorSetor['labels']) . "</p>";
            echo "<p>Dados: " . implode(', ', $chamadosPorSetor['data']) . "</p>";

            // Verifica chamados por mês
            $chamadosPorMes = $this->obterChamadosPorMes($empresaId, $setoresIds, $isAdmin);

            echo "<h2>Chamados por Mês</h2>";
            echo "<p>Labels: " . implode(', ', $chamadosPorMes['labels']) . "</p>";
            echo "<p>Dados: " . implode(', ', $chamadosPorMes['data']) . "</p>";

            // Verifica chamados recentes
            $chamadosRecentes = $this->obterChamadosRecentes($empresaId, $setoresIds, $isAdmin);

            echo "<h2>Chamados Recentes</h2>";
            echo "<p>Total: " . count($chamadosRecentes) . "</p>";

            if (!empty($chamadosRecentes)) {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Solicitante</th><th>Descrição</th><th>Data</th><th>Setor</th><th>Status</th></tr>";

                foreach ($chamadosRecentes as $chamado) {
                    echo "<tr>";
                    echo "<td>" . $chamado['id'] . "</td>";
                    echo "<td>" . $chamado['solicitante'] . "</td>";
                    echo "<td>" . substr($chamado['descricao'], 0, 50) . "...</td>";
                    echo "<td>" . $chamado['data_solicitacao'] . "</td>";
                    echo "<td>" . $chamado['setor'] . "</td>";
                    echo "<td>" . $chamado['status'] . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Nenhum chamado recente encontrado.</p>";
            }

            // Verifica a tabela usuarios_setores
            echo "<h2>Registros na Tabela usuarios_setores</h2>";

            $sql = "SELECT * FROM usuarios_setores WHERE usuario_id = :usuario_id";
            $usuariosSetores = $this->chamadoModel->executeQuery($sql, ['usuario_id' => $usuarioId]);

            if (!empty($usuariosSetores)) {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Usuário ID</th><th>Setor ID</th><th>Principal</th></tr>";

                foreach ($usuariosSetores as $registro) {
                    echo "<tr>";
                    echo "<td>" . $registro['id'] . "</td>";
                    echo "<td>" . $registro['usuario_id'] . "</td>";
                    echo "<td>" . $registro['setor_id'] . "</td>";
                    echo "<td>" . ($registro['principal'] ? 'Sim' : 'Não') . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Nenhum registro encontrado na tabela usuarios_setores para este usuário.</p>";
            }
        } catch (Exception $e) {
            echo "<h2>Erro</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }

        exit;
    }
}
