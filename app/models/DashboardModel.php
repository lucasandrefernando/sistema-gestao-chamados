<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para o Dashboard
 */
class DashboardModel extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('chamados'); // Usar a tabela chamados como base
    }

    /**
     * Obtém estatísticas gerais para o dashboard
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com estatísticas
     */
    public function getEstatisticas($empresaId)
    {
        $estatisticas = [];

        try {
            // Verifica a estrutura da tabela
            $estrutura = $this->verificarEstruturaChamados();

            // Define o campo de data a ser usado
            $campoData = $estrutura['tem_data_criacao'] ? 'data_criacao' : 'data_solicitacao';
            $campoDataConclusao = $estrutura['tem_data_conclusao'] ? 'data_conclusao' : 'data_atualizacao';
            $campoTitulo = $estrutura['tem_titulo'] ? 'titulo' : 'descricao';
            $campoSolicitante = $estrutura['tem_solicitante'] ? 'solicitante' : 'titulo';

            // Total de chamados
            $sql = "SELECT COUNT(*) as total FROM chamados WHERE empresa_id = :empresa_id AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $estatisticas['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados abertos
            $sql = "SELECT COUNT(*) as total FROM chamados WHERE empresa_id = :empresa_id AND status_id = 1 AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $estatisticas['abertos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados em andamento
            $sql = "SELECT COUNT(*) as total FROM chamados WHERE empresa_id = :empresa_id AND status_id = 2 AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $estatisticas['em_andamento'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados concluídos
            $sql = "SELECT COUNT(*) as total FROM chamados WHERE empresa_id = :empresa_id AND status_id = 4 AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $estatisticas['concluidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados concluídos hoje
            $hoje = date('Y-m-d');
            $sql = "SELECT COUNT(*) as total FROM chamados 
                WHERE empresa_id = :empresa_id 
                AND status_id = 4 
                AND DATE({$campoDataConclusao}) = :hoje 
                AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':hoje', $hoje, PDO::PARAM_STR);
            $stmt->execute();
            $estatisticas['concluidos_hoje'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tempo médio de atendimento (em horas)
            $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, {$campoData}, {$campoDataConclusao})) as tempo_medio 
                FROM chamados 
                WHERE empresa_id = :empresa_id 
                AND status_id = 4 
                AND {$campoDataConclusao} IS NOT NULL 
                AND (removido = 0 OR removido IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $tempoMedio = $stmt->fetch(PDO::FETCH_ASSOC)['tempo_medio'] ?? 0;
            $estatisticas['tempo_medio_atendimento'] = round($tempoMedio, 1);

            // Chamados recentes
            $sql = "SELECT c.id, c.{$campoSolicitante} as solicitante, c.descricao, c.{$campoData} as data_solicitacao, c.status_id, 
                       s.nome as setor, st.nome as status, st.cor as status_cor
                FROM chamados c
                LEFT JOIN setores s ON c.setor_id = s.id
                LEFT JOIN status_chamado st ON c.status_id = st.id
                WHERE c.empresa_id = :empresa_id AND (c.removido = 0 OR removido IS NULL)
                ORDER BY c.{$campoData} DESC
                LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $estatisticas['recentes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $estatisticas;
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por status
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getChamadosPorStatus($empresaId)
    {
        try {
            $sql = "SELECT st.id, st.nome, st.cor, COUNT(c.id) as total
                    FROM status_chamado st
                    LEFT JOIN chamados c ON st.id = c.status_id AND c.empresa_id = :empresa_id AND (c.removido = 0 OR c.removido IS NULL)
                    GROUP BY st.id
                    ORDER BY st.id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($result as $row) {
                $labels[] = $row['nome'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $row['cor'] ?? $this->getRandomColor();
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por setor
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getChamadosPorSetor($empresaId)
    {
        try {
            $sql = "SELECT s.id, s.nome, COUNT(c.id) as total
                    FROM setores s
                    LEFT JOIN chamados c ON s.id = c.setor_id AND (c.removido = 0 OR c.removido IS NULL)
                    WHERE s.empresa_id = :empresa_id AND (s.removido = 0 OR s.removido IS NULL)
                    GROUP BY s.id
                    ORDER BY total DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($result as $row) {
                $labels[] = $row['nome'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $this->getRandomColor();
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por setor: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por mês
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getChamadosPorMes($empresaId)
    {
        try {
            // Verifica a estrutura da tabela
            $estrutura = $this->verificarEstruturaChamados();

            // Define o campo de data a ser usado
            $campoData = $estrutura['tem_data_criacao'] ? 'data_criacao' : 'data_solicitacao';

            $sql = "SELECT MONTH({$campoData}) as mes, COUNT(*) as total
                FROM chamados
                WHERE empresa_id = :empresa_id 
                AND YEAR({$campoData}) = YEAR(CURRENT_DATE)
                AND (removido = 0 OR removido IS NULL)
                GROUP BY MONTH({$campoData})
                ORDER BY mes";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Inicializa array com todos os meses
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

            foreach ($result as $row) {
                $mesIndex = (int)$row['mes'] - 1; // Ajusta para índice 0-11
                $data[$mesIndex] = (int)$row['total'];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => '#4361ee'
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de tempo médio de atendimento por setor
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getTempoMedioPorSetor($empresaId)
    {
        try {
            // Verifica a estrutura da tabela
            $estrutura = $this->verificarEstruturaChamados();

            // Define o campo de data a ser usado
            $campoData = $estrutura['tem_data_criacao'] ? 'data_criacao' : 'data_solicitacao';
            $campoDataConclusao = $estrutura['tem_data_conclusao'] ? 'data_conclusao' : 'data_atualizacao';

            $sql = "SELECT s.id, s.nome, 
                       AVG(TIMESTAMPDIFF(HOUR, c.{$campoData}, c.{$campoDataConclusao})) as tempo_medio
                FROM setores s
                LEFT JOIN chamados c ON s.id = c.setor_id 
                                    AND c.status_id = 4 
                                    AND c.{$campoDataConclusao} IS NOT NULL
                                    AND (c.removido = 0 OR c.removido IS NULL)
                WHERE s.empresa_id = :empresa_id AND (s.removido = 0 OR s.removido IS NULL)
                GROUP BY s.id
                HAVING tempo_medio IS NOT NULL
                ORDER BY tempo_medio ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($result as $row) {
                $labels[] = $row['nome'];
                $data[] = round((float)$row['tempo_medio'], 1);
                $backgroundColor[] = $this->getRandomColor();
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter tempo médio por setor: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por tipo de serviço
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getChamadosPorTipoServico($empresaId)
    {
        try {
            $sql = "SELECT tipo_servico, COUNT(*) as total
                    FROM chamados
                    WHERE empresa_id = :empresa_id 
                    AND tipo_servico IS NOT NULL 
                    AND tipo_servico != ''
                    AND (removido = 0 OR removido IS NULL)
                    GROUP BY tipo_servico
                    ORDER BY total DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($result as $row) {
                $labels[] = $row['tipo_servico'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $this->getRandomColor();
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por tipo de serviço: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados para o gráfico de chamados por dia da semana
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com dados para o gráfico
     */
    public function getChamadosPorDiaSemana($empresaId)
    {
        try {
            // Verifica a estrutura da tabela
            $estrutura = $this->verificarEstruturaChamados();

            // Define o campo de data a ser usado
            $campoData = $estrutura['tem_data_criacao'] ? 'data_criacao' : 'data_solicitacao';

            $sql = "SELECT DAYOFWEEK({$campoData}) as dia_semana, COUNT(*) as total
                FROM chamados
                WHERE empresa_id = :empresa_id 
                AND (removido = 0 OR removido IS NULL)
                GROUP BY DAYOFWEEK({$campoData})
                ORDER BY dia_semana";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Mapeia os dias da semana (DAYOFWEEK retorna 1=Domingo, 2=Segunda, etc.)
            $diasSemana = [
                1 => 'Domingo',
                2 => 'Segunda',
                3 => 'Terça',
                4 => 'Quarta',
                5 => 'Quinta',
                6 => 'Sexta',
                7 => 'Sábado'
            ];

            $data = array_fill(0, 7, 0);

            foreach ($result as $row) {
                $diaIndex = (int)$row['dia_semana'] - 1; // Ajusta para índice 0-6
                $data[$diaIndex] = (int)$row['total'];
            }

            return [
                'labels' => array_values($diasSemana),
                'data' => $data,
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
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por dia da semana: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém atividades recentes (chamados, comentários, etc.)
     * 
     * @param int $empresaId ID da empresa
     * @return array Array com atividades recentes
     */
    public function getRecentActivities($empresaId)
    {
        try {
            // Últimos chamados criados
            $sql = "SELECT c.id, c.titulo as solicitante, 'novo_chamado' as tipo, c.data_criacao as data,
                           s.nome as setor, st.nome as status
                    FROM chamados c
                    LEFT JOIN setores s ON c.setor_id = s.id
                    LEFT JOIN status_chamado st ON c.status_id = st.id
                    WHERE c.empresa_id = :empresa_id AND (c.removido = 0 OR c.removido IS NULL)
                    ORDER BY c.data_criacao DESC
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Últimos comentários
            $sql = "SELECT cc.id, cc.chamado_id, cc.comentario, cc.data_criacao as data,
                           u.nome as usuario, 'comentario' as tipo
                    FROM chamado_comentarios cc
                    LEFT JOIN chamados c ON cc.chamado_id = c.id
                    LEFT JOIN usuarios u ON cc.usuario_id = u.id
                    WHERE c.empresa_id = :empresa_id AND (c.removido = 0 OR c.removido IS NULL)
                    ORDER BY cc.data_criacao DESC
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Últimas mudanças de status
            $sql = "SELECT ch.id, ch.chamado_id, ch.acao, ch.detalhes, ch.data_criacao as data,
                           u.nome as usuario, 'status_change' as tipo
                    FROM chamado_historico ch
                    LEFT JOIN chamados c ON ch.chamado_id = c.id
                    LEFT JOIN usuarios u ON ch.usuario_id = u.id
                    WHERE c.empresa_id = :empresa_id 
                    AND (c.removido = 0 OR c.removido IS NULL)
                    AND ch.acao = 'Status alterado'
                    ORDER BY ch.data_criacao DESC
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $statusChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Combina todos os resultados e ordena por data
            $atividades = array_merge($chamados, $comentarios, $statusChanges);

            usort($atividades, function ($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });

            // Limita a 10 atividades
            return array_slice($atividades, 0, 10);
        } catch (Exception $e) {
            error_log('Erro ao obter atividades recentes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gera uma cor aleatória em formato hexadecimal
     * 
     * @return string Cor em formato hexadecimal
     */
    private function getRandomColor()
    {
        // Lista de cores predefinidas para melhor harmonia visual
        $colors = [
            '#4361ee',
            '#3a0ca3',
            '#7209b7',
            '#f72585',
            '#4cc9f0',
            '#4895ef',
            '#560bad',
            '#f15bb5',
            '#fee440',
            '#00bbf9',
            '#00f5d4',
            '#e63946',
            '#457b9d',
            '#1d3557',
            '#f1faee',
            '#2a9d8f',
            '#e9c46a',
            '#f4a261',
            '#e76f51',
            '#264653'
        ];

        return $colors[array_rand($colors)];
    }

    /**
     * Verifica a estrutura da tabela de chamados e adapta as consultas conforme necessário
     * 
     * @return array Informações sobre a estrutura da tabela
     */
    public function verificarEstruturaChamados()
    {
        try {
            // Verifica se a tabela chamados existe
            $sql = "SHOW TABLES LIKE 'chamados'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tabelaExiste = $stmt->rowCount() > 0;

            if (!$tabelaExiste) {
                return ['existe' => false];
            }

            // Verifica as colunas da tabela
            $sql = "SHOW COLUMNS FROM chamados";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Verifica campos específicos
            $temDataCriacao = in_array('data_criacao', $colunas);
            $temDataSolicitacao = in_array('data_solicitacao', $colunas);
            $temDataConclusao = in_array('data_conclusao', $colunas);
            $temTitulo = in_array('titulo', $colunas);
            $temSolicitante = in_array('solicitante', $colunas);

            return [
                'existe' => true,
                'colunas' => $colunas,
                'tem_data_criacao' => $temDataCriacao,
                'tem_data_solicitacao' => $temDataSolicitacao,
                'tem_data_conclusao' => $temDataConclusao,
                'tem_titulo' => $temTitulo,
                'tem_solicitante' => $temSolicitante
            ];
        } catch (Exception $e) {
            error_log('Erro ao verificar estrutura da tabela chamados: ' . $e->getMessage());
            return ['existe' => false, 'erro' => $e->getMessage()];
        }
    }
}
