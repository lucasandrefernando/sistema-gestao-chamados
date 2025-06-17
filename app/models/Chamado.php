<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para gerenciamento de chamados
 */
class Chamado extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('chamados');
    }

    /**
     * Obtém estatísticas para o dashboard
     * 
     * @param int $empresaId ID da empresa
     * @return array Estatísticas
     */
    public function getEstatisticas($empresaId)
    {
        try {
            // Total de chamados
            $total = $this->count('empresa_id = :empresa_id', ['empresa_id' => $empresaId]);

            // Chamados por status
            // Abertos (status_id = 1)
            $sqlAbertos = "SELECT COUNT(*) as total FROM {$this->table} 
                      WHERE empresa_id = :empresa_id AND status_id = 1";
            $stmtAbertos = $this->db->prepare($sqlAbertos);
            $stmtAbertos->execute(['empresa_id' => $empresaId]);
            $abertos = $stmtAbertos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Em andamento (status_id = 2)
            $sqlEmAndamento = "SELECT COUNT(*) as total FROM {$this->table} 
                          WHERE empresa_id = :empresa_id AND status_id = 2";
            $stmtEmAndamento = $this->db->prepare($sqlEmAndamento);
            $stmtEmAndamento->execute(['empresa_id' => $empresaId]);
            $emAndamento = $stmtEmAndamento->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Concluídos (status_id = 4)
            $sqlConcluidos = "SELECT COUNT(*) as total FROM {$this->table} 
                         WHERE empresa_id = :empresa_id AND status_id = 4";
            $stmtConcluidos = $this->db->prepare($sqlConcluidos);
            $stmtConcluidos->execute(['empresa_id' => $empresaId]);
            $concluidos = $stmtConcluidos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            return [
                'total' => $total,
                'abertos' => $abertos,
                'em_andamento' => $emAndamento,
                'concluidos' => $concluidos
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0
            ];
        }
    }




    /**
     * Obtém os anos disponíveis para filtro
     * 
     * @param int $empresaId ID da empresa
     * @return array Anos disponíveis
     */
    public function getAnosDisponiveis($empresaId)
    {
        try {
            $sql = "SELECT DISTINCT YEAR(data_solicitacao) as ano 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                ORDER BY ano DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Se não houver dados, retorna o ano atual
            if (empty($result)) {
                return [date('Y')];
            }

            return $result;
        } catch (Exception $e) {
            error_log('Erro ao obter anos disponíveis: ' . $e->getMessage());
            return [date('Y')];
        }
    }

    /**
     * Obtém dados de chamados por status para gráficos
     * 
     * @param int $empresaId ID da empresa
     * @return array Dados para gráfico
     */
    public function getChamadosPorStatus($empresaId)
    {
        try {
            $sql = "SELECT s.id, s.nome, COUNT(*) as total 
                FROM {$this->table} c
                JOIN status_chamados s ON c.status_id = s.id
                WHERE c.empresa_id = :empresa_id 
                GROUP BY s.id, s.nome
                ORDER BY s.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [
                1 => 'rgba(255, 99, 132, 0.7)',  // Aberto
                2 => 'rgba(54, 162, 235, 0.7)',  // Em andamento
                3 => 'rgba(255, 206, 86, 0.7)',  // Pendente
                4 => 'rgba(75, 192, 192, 0.7)',  // Concluído
                5 => 'rgba(201, 203, 207, 0.7)'  // Cancelado
            ];
            $colors = [];

            foreach ($result as $row) {
                $labels[] = $row['nome'];
                $data[] = (int)$row['total'];
                $colors[] = $backgroundColor[$row['id']] ?? 'rgba(153, 102, 255, 0.7)';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $colors
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
     * Obtém todos os chamados de um setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Chamados do setor
     */
    public function getChamadosPorSetor($empresaId, $setorId)
    {
        try {
            // Verifica se a tabela chamados existe
            $tableExists = false;
            try {
                $this->db->query("SELECT 1 FROM {$this->table} LIMIT 1");
                $tableExists = true;
            } catch (PDOException $e) {
                return [];
            }

            if (!$tableExists) {
                return [];
            }

            $sql = "SELECT 
                c.id, 
                c.descricao as titulo, 
                c.descricao,
                c.data_solicitacao as data_abertura,
                c.data_conclusao,
                c.status_id,
                s.nome as status_nome,
                c.solicitante as solicitante_nome
            FROM {$this->table} c
            LEFT JOIN status_chamados s ON c.status_id = s.id
            WHERE c.empresa_id = :empresa_id AND c.setor_id = :setor_id
            ORDER BY c.data_solicitacao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);

            $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adiciona o campo 'status' baseado em 'status_nome' para compatibilidade
            foreach ($chamados as &$chamado) {
                if (isset($chamado['status_nome'])) {
                    $chamado['status'] = strtolower(str_replace(' ', '_', $chamado['status_nome']));
                } else {
                    $chamado['status'] = 'desconhecido';
                }
            }

            return $chamados;
        } catch (Exception $e) {
            error_log('Erro ao obter chamados do setor: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém dados de chamados por mês para gráficos com filtro de ano
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorMes($empresaId, $ano = null)
    {
        try {
            // Se não for informado o ano, usa o ano atual
            if (!$ano) {
                $ano = date('Y');
            }

            $sql = "SELECT 
                    MONTH(data_solicitacao) as mes, 
                    COUNT(*) as total 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND YEAR(data_solicitacao) = :ano
                GROUP BY MONTH(data_solicitacao)
                ORDER BY mes";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'ano' => $ano
            ]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];

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

            // Inicializa todos os meses com zero
            foreach ($meses as $numMes => $nomeMes) {
                $labels[$numMes] = $nomeMes;
                $data[$numMes] = 0;
            }

            // Preenche com os dados reais
            foreach ($result as $row) {
                $data[$row['mes']] = (int)$row['total'];
            }

            return [
                'labels' => array_values($labels),
                'data' => array_values($data),
                'ano' => $ano
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'ano' => $ano
            ];
        }
    }

    /**
     * Obtém atividades recentes (últimos chamados, comentários, etc.)
     * 
     * @param int $empresaId ID da empresa
     * @return array Atividades recentes
     */
    public function getRecentActivities($empresaId)
    {
        try {
            // Últimos chamados
            $sql = "SELECT c.id, c.descricao as titulo, s.nome as status, c.data_solicitacao as data, 
                    c.solicitante as solicitante_nome, 
                    'chamado' as tipo,
                    c.data_solicitacao as data
                FROM {$this->table} c
                LEFT JOIN status_chamados s ON c.status_id = s.id
                WHERE c.empresa_id = :empresa_id
                
                UNION
                
                SELECT c.id, c.descricao as titulo, s.nome as status, cc.data_criacao, 
                    u.nome as usuario_nome,
                    'comentario' as tipo,
                    cc.data_criacao as data
                FROM chamados_comentarios cc
                JOIN {$this->table} c ON cc.chamado_id = c.id
                LEFT JOIN status_chamados s ON c.status_id = s.id
                LEFT JOIN usuarios u ON cc.usuario_id = u.id
                WHERE c.empresa_id = :empresa_id
                
                ORDER BY data DESC
                LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter atividades recentes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Formata o status para exibição
     * 
     * @param string $status Status do chamado
     * @return string Status formatado
     */
    private function formatarStatus($status)
    {
        $formatado = [
            'aberto' => 'Aberto',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado'
        ];

        return $formatado[$status] ?? $status;
    }

    /**
     * Obtém chamados por status e setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Dados para gráfico
     */
    public function getChamadosPorStatusESetor($empresaId, $setorId)
    {
        try {
            $sql = "SELECT 
                s.id as status_id,
                s.nome as nome,
                LOWER(REPLACE(s.nome, ' ', '_')) as status,
                COUNT(*) as total,
                (COUNT(*) * 100.0 / (
                    SELECT COUNT(*) 
                    FROM {$this->table} 
                    WHERE empresa_id = :empresa_id AND setor_id = :setor_id
                )) as percentual
            FROM {$this->table} c
            JOIN status_chamados s ON c.status_id = s.id
            WHERE c.empresa_id = :empresa_id AND c.setor_id = :setor_id
            GROUP BY s.id, s.nome";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status e setor: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém chamados recentes por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @param int $limit Limite de chamados
     * @return array Chamados recentes
     */
    public function getChamadosRecentesPorSetor($empresaId, $setorId, $limit = 5)
    {
        try {
            $sql = "SELECT 
                c.id, 
                c.descricao as titulo, 
                c.solicitante as solicitante_nome, 
                c.data_solicitacao as data_abertura,
                c.status_id,
                s.nome as status_nome
            FROM {$this->table} c
            LEFT JOIN status_chamados s ON c.status_id = s.id
            WHERE c.empresa_id = :empresa_id AND c.setor_id = :setor_id
            ORDER BY c.data_solicitacao DESC
            LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindParam(':setor_id', $setorId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter chamados recentes por setor: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém o tempo médio de atendimento por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return int Tempo médio em minutos
     */
    public function getTempoMedioAtendimentoPorSetor($empresaId, $setorId)
    {
        try {
            $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, data_solicitacao, data_conclusao)) as tempo_medio
            FROM {$this->table}
            WHERE empresa_id = :empresa_id 
            AND setor_id = :setor_id
            AND data_conclusao IS NOT NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['tempo_medio'] ? round($result['tempo_medio']) : 0;
        } catch (Exception $e) {
            error_log('Erro ao obter tempo médio de atendimento: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtém chamados por prioridade e setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Dados para gráfico
     */
    public function getChamadosPorPrioridadeESetor($empresaId, $setorId)
    {
        try {
            // Como a tabela não tem campo prioridade, vamos criar dados fictícios
            // baseados no tipo_servico
            $sql = "SELECT 
                COALESCE(c.tipo_servico, 'Outros') as tipo_servico,
                COUNT(*) as total
            FROM {$this->table} c
            WHERE c.empresa_id = :empresa_id AND c.setor_id = :setor_id
            GROUP BY c.tipo_servico";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Mapeia tipo_servico para prioridade para compatibilidade
            $prioridades = [];
            foreach ($result as $row) {
                $prioridades[] = [
                    'prioridade' => strtolower(str_replace(' ', '_', $row['tipo_servico'])),
                    'nome' => $row['tipo_servico'],
                    'total' => $row['total']
                ];
            }

            return $prioridades;
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por prioridade: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém chamados por mês e setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Dados para gráfico
     */
    public function getChamadosPorMesESetor($empresaId, $setorId)
    {
        try {
            $sql = "SELECT 
                DATE_FORMAT(data_solicitacao, '%m/%Y') as mes_ano,
                COUNT(*) as total
            FROM {$this->table}
            WHERE empresa_id = :empresa_id AND setor_id = :setor_id
            GROUP BY mes_ano
            ORDER BY YEAR(data_solicitacao), MONTH(data_solicitacao)
            LIMIT 12";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém usuários mais ativos por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Usuários mais ativos
     */
    public function getUsuariosMaisAtivosPorSetor($empresaId, $setorId)
    {
        try {
            // Como não temos campo atendente_id, vamos usar solicitante
            $sql = "SELECT 
                c.solicitante as nome,
                COUNT(c.id) as total
            FROM {$this->table} c
            WHERE c.empresa_id = :empresa_id AND c.setor_id = :setor_id
            GROUP BY c.solicitante
            ORDER BY total DESC
            LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'empresa_id' => $empresaId,
                'setor_id' => $setorId
            ]);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adiciona um id fictício para compatibilidade
            foreach ($result as &$row) {
                $row['id'] = 0;
            }

            return $result;
        } catch (Exception $e) {
            error_log('Erro ao obter usuários mais ativos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém os tipos de serviço disponíveis
     * 
     * @param int $empresaId ID da empresa
     * @return array Lista de tipos de serviço
     */
    public function getTiposServico($empresaId)
    {
        try {
            $sql = "SELECT DISTINCT tipo_servico 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND tipo_servico IS NOT NULL 
                AND tipo_servico != '' 
                ORDER BY tipo_servico";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);

            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $result;
        } catch (Exception $e) {
            error_log('Erro ao obter tipos de serviço: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Obtém os solicitantes disponíveis
     * 
     * @param int $empresaId ID da empresa
     * @return array Lista de solicitantes
     */
    public function getSolicitantes($empresaId)
    {
        try {
            $sql = "SELECT DISTINCT solicitante 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND solicitante IS NOT NULL 
                AND solicitante != '' 
                ORDER BY solicitante";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);

            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $result;
        } catch (Exception $e) {
            error_log('Erro ao obter solicitantes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém dados para relatório de chamados
     * 
     * @param int $empresaId ID da empresa
     * @param int|null $setorId ID do setor (opcional)
     * @param string|null $dataInicio Data inicial (opcional)
     * @param string|null $dataFim Data final (opcional)
     * @return array Dados do relatório
     */
    public function getDadosRelatorio($empresaId, $setorId = null, $dataInicio = null, $dataFim = null)
    {
        try {
            // Constrói a condição de filtro
            $condicao = 'empresa_id = :empresa_id';
            $params = ['empresa_id' => $empresaId];

            if ($setorId) {
                $condicao .= ' AND setor_id = :setor_id';
                $params['setor_id'] = $setorId;
            }

            if ($dataInicio) {
                $condicao .= ' AND data_solicitacao >= :data_inicio';
                $params['data_inicio'] = $dataInicio . ' 00:00:00';
            }

            if ($dataFim) {
                $condicao .= ' AND data_solicitacao <= :data_fim';
                $params['data_fim'] = $dataFim . ' 23:59:59';
            }

            // Total de chamados
            $totalChamados = $this->count($condicao, $params);

            // Chamados por status
            $sql = "SELECT s.id, s.nome, COUNT(*) as total
                FROM {$this->table} c
                JOIN status_chamados s ON c.status_id = s.id
                WHERE $condicao
                GROUP BY s.id, s.nome
                ORDER BY s.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $chamadosPorStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Chamados por setor
            $sqlSetor = "SELECT s.id, s.nome, COUNT(*) as total
                    FROM {$this->table} c
                    JOIN setores s ON c.setor_id = s.id
                    WHERE $condicao
                    GROUP BY s.id, s.nome
                    ORDER BY total DESC";

            $stmt = $this->db->prepare($sqlSetor);
            $stmt->execute($params);
            $chamadosPorSetor = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tempo médio de atendimento
            $sqlTempo = "SELECT 
                        AVG(TIMESTAMPDIFF(MINUTE, data_solicitacao, data_conclusao)) as tempo_medio,
                        MIN(TIMESTAMPDIFF(MINUTE, data_solicitacao, data_conclusao)) as tempo_minimo,
                        MAX(TIMESTAMPDIFF(MINUTE, data_solicitacao, data_conclusao)) as tempo_maximo
                    FROM {$this->table}
                    WHERE $condicao
                    AND data_conclusao IS NOT NULL";

            $stmt = $this->db->prepare($sqlTempo);
            $stmt->execute($params);
            $tempoAtendimento = $stmt->fetch(PDO::FETCH_ASSOC);

            // Chamados por dia da semana
            $sqlDiaSemana = "SELECT 
                            DAYOFWEEK(data_solicitacao) as dia_semana,
                            COUNT(*) as total
                        FROM {$this->table}
                        WHERE $condicao
                        GROUP BY dia_semana
                        ORDER BY dia_semana";

            $stmt = $this->db->prepare($sqlDiaSemana);
            $stmt->execute($params);
            $chamadosPorDiaSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dias da semana
            $diasSemana = [
                1 => 'Domingo',
                2 => 'Segunda',
                3 => 'Terça',
                4 => 'Quarta',
                5 => 'Quinta',
                6 => 'Sexta',
                7 => 'Sábado'
            ];

            foreach ($chamadosPorDiaSemana as &$dia) {
                $dia['nome'] = $diasSemana[$dia['dia_semana']];
            }

            // Chamados por hora do dia
            $sqlHora = "SELECT 
                        HOUR(data_solicitacao) as hora,
                        COUNT(*) as total
                    FROM {$this->table}
                    WHERE $condicao
                    GROUP BY hora
                    ORDER BY hora";

            $stmt = $this->db->prepare($sqlHora);
            $stmt->execute($params);
            $chamadosPorHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata as horas
            foreach ($chamadosPorHora as &$hora) {
                $hora['nome'] = sprintf('%02d:00', $hora['hora']);
            }

            return [
                'total_chamados' => $totalChamados,
                'chamados_por_status' => $chamadosPorStatus,
                'chamados_por_setor' => $chamadosPorSetor,
                'tempo_atendimento' => $tempoAtendimento,
                'chamados_por_dia_semana' => $chamadosPorDiaSemana,
                'chamados_por_hora' => $chamadosPorHora
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter dados para relatório: ' . $e->getMessage());
            return [
                'total_chamados' => 0,
                'chamados_por_status' => [],
                'chamados_por_setor' => [],
                'tempo_atendimento' => [
                    'tempo_medio' => 0,
                    'tempo_minimo' => 0,
                    'tempo_maximo' => 0
                ],
                'chamados_por_dia_semana' => [],
                'chamados_por_hora' => []
            ];
        }
    }

    /**
     * Insere um novo registro na tabela
     * 
     * @param array $data Dados a serem inseridos
     * @return int ID do registro inserido
     * @throws Exception Se ocorrer um erro durante a inserção
     */
    public function insert($data)
    {
        try {
            // Filtra apenas as colunas que existem na tabela
            $columns = [];
            $values = [];
            $placeholders = [];

            // Obtém as colunas da tabela
            $tableColumns = $this->getTableColumns();

            foreach ($data as $key => $value) {
                // Verifica se a coluna existe na tabela
                if (in_array($key, $tableColumns)) {
                    $columns[] = $key;
                    $placeholders[] = ":$key";
                    $values[":$key"] = $value;
                }
            }

            if (empty($columns)) {
                throw new Exception("Nenhuma coluna válida para inserção");
            }

            $columnsStr = implode(', ', $columns);
            $placeholdersStr = implode(', ', $placeholders);

            $sql = "INSERT INTO {$this->table} ($columnsStr) VALUES ($placeholdersStr)";

            error_log("SQL Insert: $sql");
            error_log("Valores: " . print_r($values, true));

            $stmt = $this->db->prepare($sql);

            foreach ($values as $key => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }

                $stmt->bindValue($key, $value, $type);
            }

            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro na execução do SQL: " . $errorInfo[2]);
            }

            $id = $this->db->lastInsertId();
            error_log("ID inserido: $id");

            return $id;
        } catch (PDOException $e) {
            error_log("PDOException ao inserir em {$this->table}: " . $e->getMessage());
            throw new Exception("Erro ao inserir registro: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Exception ao inserir em {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém as colunas da tabela
     * 
     * @return array Lista de colunas da tabela
     */
    private function getTableColumns()
    {
        try {
            $sql = "SHOW COLUMNS FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns;
        } catch (PDOException $e) {
            error_log("Erro ao obter colunas da tabela {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se a conexão com o banco de dados está funcionando
     * 
     * @return bool True se a conexão estiver funcionando, False caso contrário
     */
    public function testConnection()
    {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            return false;
        }
    }




    /**
     * Insere um novo chamado
     * 
     * @param array $data Dados do chamado
     * @return int ID do chamado inserido
     */
    public function insertChamado($data)
    {
        try {
            // Verifica se os campos obrigatórios estão presentes
            $requiredFields = ['empresa_id', 'setor_id', 'status_id', 'solicitante', 'descricao', 'data_solicitacao'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Campo obrigatório não informado: $field");
                }
            }

            // Constrói a query manualmente
            $sql = "INSERT INTO {$this->table} (
                    empresa_id, setor_id, status_id, solicitante, 
                    paciente, quarto_leito, descricao, tipo_servico, 
                    data_solicitacao, data_criacao, data_atualizacao
                ) VALUES (
                    :empresa_id, :setor_id, :status_id, :solicitante, 
                    :paciente, :quarto_leito, :descricao, :tipo_servico, 
                    :data_solicitacao, :data_criacao, :data_atualizacao
                )";

            $stmt = $this->db->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindValue(':empresa_id', $data['empresa_id'], PDO::PARAM_INT);
            $stmt->bindValue(':setor_id', $data['setor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':status_id', $data['status_id'], PDO::PARAM_INT);
            $stmt->bindValue(':solicitante', $data['solicitante'], PDO::PARAM_STR);
            $stmt->bindValue(':paciente', $data['paciente'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':quarto_leito', $data['quarto_leito'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $data['descricao'], PDO::PARAM_STR);
            $stmt->bindValue(':tipo_servico', $data['tipo_servico'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':data_solicitacao', $data['data_solicitacao'], PDO::PARAM_STR);
            $stmt->bindValue(':data_criacao', $data['data_criacao'], PDO::PARAM_STR);
            $stmt->bindValue(':data_atualizacao', $data['data_atualizacao'], PDO::PARAM_STR);

            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro na execução do SQL: " . $errorInfo[2]);
            }

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao inserir chamado: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Insere um novo registro de histórico
     * 
     * @param array $data Dados do histórico
     * @return int ID do histórico inserido
     */
    public function insertHistorico($data)
    {
        try {
            // Verifica se os campos obrigatórios estão presentes
            $requiredFields = ['chamado_id', 'setor_id_anterior', 'setor_id_novo', 'status_id_anterior', 'status_id_novo'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Campo obrigatório não informado: $field");
                }
            }

            // Constrói a query manualmente
            $sql = "INSERT INTO {$this->table} (
                    chamado_id, setor_id_anterior, setor_id_novo, 
                    status_id_anterior, status_id_novo, usuario_id, 
                    observacao, data_criacao
                ) VALUES (
                    :chamado_id, :setor_id_anterior, :setor_id_novo, 
                    :status_id_anterior, :status_id_novo, :usuario_id, 
                    :observacao, :data_criacao
                )";

            $stmt = $this->db->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindValue(':chamado_id', $data['chamado_id'], PDO::PARAM_INT);
            $stmt->bindValue(':setor_id_anterior', $data['setor_id_anterior'], PDO::PARAM_INT);
            $stmt->bindValue(':setor_id_novo', $data['setor_id_novo'], PDO::PARAM_INT);
            $stmt->bindValue(':status_id_anterior', $data['status_id_anterior'], PDO::PARAM_INT);
            $stmt->bindValue(':status_id_novo', $data['status_id_novo'], PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $data['usuario_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':observacao', $data['observacao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':data_criacao', $data['data_criacao'], PDO::PARAM_STR);

            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro na execução do SQL: " . $errorInfo[2]);
            }

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao inserir histórico: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Insere um novo comentário
     * 
     * @param array $data Dados do comentário
     * @return int ID do comentário inserido
     */
    public function insertComentario($data)
    {
        try {
            // Verifica se os campos obrigatórios estão presentes
            $requiredFields = ['chamado_id', 'comentario'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Campo obrigatório não informado: $field");
                }
            }

            // Constrói a query manualmente
            $sql = "INSERT INTO {$this->table} (
                    chamado_id, usuario_id, comentario, data_criacao
                ) VALUES (
                    :chamado_id, :usuario_id, :comentario, :data_criacao
                )";

            $stmt = $this->db->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindValue(':chamado_id', $data['chamado_id'], PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $data['usuario_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':comentario', $data['comentario'], PDO::PARAM_STR);
            $stmt->bindValue(':data_criacao', $data['data_criacao'], PDO::PARAM_STR);

            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro na execução do SQL: " . $errorInfo[2]);
            }

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao inserir comentário: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém dados de chamados por status para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtro
     * @param int|null $mes Mês para filtro (opcional)
     * @param int|null $setorId ID do setor para filtro (opcional)
     * @param string|null $condicaoAdicional Condição SQL adicional (opcional)
     * @param array|null $paramsAdicionais Parâmetros adicionais para a condição (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorStatusRelatorio($empresaId, $ano, $mes = null, $setorId = null, $condicaoAdicional = null, $paramsAdicionais = null)
    {
        try {
            // Se for fornecida uma condição adicional, use-a
            if ($condicaoAdicional && $paramsAdicionais) {
                $condicao = $condicaoAdicional;
                $params = $paramsAdicionais;
            } else {
                // Caso contrário, use a condição padrão
                $condicao = "c.empresa_id = :empresa_id AND YEAR(c.data_solicitacao) = :ano";
                $params = [
                    'empresa_id' => $empresaId,
                    'ano' => $ano
                ];

                if ($mes !== null) {
                    $condicao .= " AND MONTH(c.data_solicitacao) = :mes";
                    $params['mes'] = $mes;
                }

                if ($setorId !== null) {
                    $condicao .= " AND c.setor_id = :setor_id";
                    $params['setor_id'] = $setorId;
                }
            }

            $sql = "SELECT 
                s.id as status_id,
                s.nome as status_nome,
                COUNT(*) as total
            FROM {$this->table} c
            JOIN status_chamados s ON c.status_id = s.id
            WHERE $condicao
            GROUP BY s.id, s.nome
            ORDER BY s.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            // Cores específicas para status comuns
            $statusColors = [
                'aberto' => 'rgba(255, 99, 132, 0.7)',       // Vermelho
                'andamento' => 'rgba(255, 206, 86, 0.7)',    // Amarelo
                'atendimento' => 'rgba(255, 206, 86, 0.7)',  // Amarelo
                'concluído' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'resolvido' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'cancelado' => 'rgba(153, 102, 255, 0.7)',   // Roxo
                'pendente' => 'rgba(54, 162, 235, 0.7)',     // Azul
            ];

            // Cores alternativas para outros status
            $alternativeColors = [
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)',  // Cinza
                'rgba(83, 123, 196, 0.7)',   // Azul escuro
                'rgba(245, 130, 49, 0.7)',   // Laranja escuro
                'rgba(22, 160, 133, 0.7)'    // Verde escuro
            ];

            $colorIndex = 0;

            foreach ($result as $row) {
                $labels[] = $row['status_nome'];
                $data[] = (int)$row['total'];

                // Determina a cor com base no nome do status
                $color = null;
                $statusNome = strtolower($row['status_nome']);

                foreach ($statusColors as $keyword => $statusColor) {
                    if (strpos($statusNome, $keyword) !== false) {
                        $color = $statusColor;
                        break;
                    }
                }

                // Se não encontrou uma cor específica, usa uma das alternativas
                if (!$color) {
                    $color = $alternativeColors[$colorIndex % count($alternativeColors)];
                    $colorIndex++;
                }

                $backgroundColor[] = $color;
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status: ' . $e->getMessage());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém dados de chamados por mês para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorMesRelatorio($empresaId, $ano, $setorId = null)
    {
        try {
            $sql = "SELECT 
                    MONTH(data_solicitacao) as mes, 
                    COUNT(*) as total 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND YEAR(data_solicitacao) = :ano";

            $params = [
                'empresa_id' => $empresaId,
                'ano' => $ano
            ];

            if ($setorId) {
                $sql .= " AND setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY MONTH(data_solicitacao)
                  ORDER BY mes";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];

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

            // Inicializa todos os meses com zero
            foreach ($meses as $numMes => $nomeMes) {
                $labels[$numMes] = $nomeMes;
                $data[$numMes] = 0;
            }

            // Preenche com os dados reais
            foreach ($result as $row) {
                $data[$row['mes']] = (int)$row['total'];
            }

            return [
                'labels' => array_values($labels),
                'data' => array_values($data),
                'ano' => $ano,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'ano' => $ano,
                'raw' => []
            ];
        }
    }

    /**
     * Obtém o tempo médio de atendimento dos chamados
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getTempoMedioAtendimento($empresaId, $ano = null, $mes = null, $setorId = null)
    {
        try {
            // Log para depuração
            error_log("Obtendo tempo médio de atendimento para empresa $empresaId");

            $sql = "SELECT 
                    s.id as status_id,
                    s.nome as status_nome,
                    AVG(
                        CASE 
                            WHEN c.data_conclusao IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, c.data_solicitacao, c.data_conclusao)
                            ELSE TIMESTAMPDIFF(HOUR, c.data_solicitacao, NOW())
                        END
                    ) as tempo_medio,
                    COUNT(*) as total_chamados
                FROM {$this->table} c
                JOIN status_chamados s ON c.status_id = s.id
                WHERE c.empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $sql .= " AND YEAR(c.data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $sql .= " AND MONTH(c.data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            if ($setorId) {
                $sql .= " AND c.setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY s.id, s.nome
                  HAVING total_chamados > 0
                  ORDER BY s.id";

            error_log("SQL: $sql");
            error_log("Params: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Resultado: " . print_r($result, true));

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            // Cores específicas para status comuns
            $statusColors = [
                'aberto' => 'rgba(255, 99, 132, 0.7)',       // Vermelho
                'andamento' => 'rgba(255, 206, 86, 0.7)',    // Amarelo
                'atendimento' => 'rgba(255, 206, 86, 0.7)',  // Amarelo
                'concluído' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'resolvido' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'cancelado' => 'rgba(153, 102, 255, 0.7)',   // Roxo
                'pendente' => 'rgba(54, 162, 235, 0.7)',     // Azul
                'pausado' => 'rgba(54, 162, 235, 0.7)',      // Azul
            ];

            // Cores alternativas para outros status
            $alternativeColors = [
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)',  // Cinza
                'rgba(83, 123, 196, 0.7)',   // Azul escuro
                'rgba(245, 130, 49, 0.7)',   // Laranja escuro
                'rgba(22, 160, 133, 0.7)'    // Verde escuro
            ];

            $colorIndex = 0;

            foreach ($result as $row) {
                $labels[] = $row['status_nome'] . ' (' . $row['total_chamados'] . ')';
                $data[] = (float)$row['tempo_medio']; // Usar float em vez de round para manter a precisão

                // Determina a cor com base no nome do status
                $color = null;
                $statusNome = strtolower($row['status_nome']);

                foreach ($statusColors as $keyword => $statusColor) {
                    if (strpos($statusNome, $keyword) !== false) {
                        $color = $statusColor;
                        break;
                    }
                }

                // Se não encontrou uma cor específica, usa uma das alternativas
                if (!$color) {
                    $color = $alternativeColors[$colorIndex % count($alternativeColors)];
                    $colorIndex++;
                }

                $backgroundColor[] = $color;
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter tempo médio de atendimento: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém dados de chamados por setor para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorSetorRelatorio($empresaId, $ano = null, $mes = null)
    {
        try {
            $sql = "SELECT 
                    s.id as setor_id,
                    s.nome as setor_nome,
                    COUNT(*) as total
                FROM {$this->table} c
                JOIN setores s ON c.setor_id = s.id
                WHERE c.empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $sql .= " AND YEAR(c.data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $sql .= " AND MONTH(c.data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            $sql .= " GROUP BY s.id, s.nome
                  ORDER BY total DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            $colors = [
                'rgba(255, 99, 132, 0.7)',   // Vermelho
                'rgba(54, 162, 235, 0.7)',   // Azul
                'rgba(255, 206, 86, 0.7)',   // Amarelo
                'rgba(75, 192, 192, 0.7)',   // Verde
                'rgba(153, 102, 255, 0.7)',  // Roxo
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)'   // Cinza
            ];

            foreach ($result as $index => $row) {
                $labels[] = $row['setor_nome'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $colors[$index % count($colors)];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por setor: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém dados de chamados por tipo de serviço para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorTipoServicoRelatorio($empresaId, $ano = null, $mes = null, $setorId = null)
    {
        try {
            // Log para depuração
            error_log("Obtendo chamados por tipo de serviço para empresa $empresaId");

            $sql = "SELECT 
                    COALESCE(tipo_servico, 'Não especificado') as tipo_servico,
                    COUNT(*) as total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $sql .= " AND YEAR(data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $sql .= " AND MONTH(data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            if ($setorId) {
                $sql .= " AND setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY tipo_servico
                  ORDER BY total DESC
                  LIMIT 10";

            error_log("SQL: $sql");
            error_log("Params: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Resultado: " . print_r($result, true));

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            $colors = [
                'rgba(255, 99, 132, 0.7)',   // Vermelho
                'rgba(54, 162, 235, 0.7)',   // Azul
                'rgba(255, 206, 86, 0.7)',   // Amarelo
                'rgba(75, 192, 192, 0.7)',   // Verde
                'rgba(153, 102, 255, 0.7)',  // Roxo
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)',  // Cinza
                'rgba(83, 123, 196, 0.7)',   // Azul escuro
                'rgba(245, 130, 49, 0.7)',   // Laranja escuro
                'rgba(22, 160, 133, 0.7)'    // Verde escuro
            ];

            foreach ($result as $index => $row) {
                $labels[] = $row['tipo_servico'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $colors[$index % count($colors)];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por tipo de serviço: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém dados de chamados por solicitante para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorSolicitanteRelatorio($empresaId, $ano = null, $mes = null, $setorId = null)
    {
        try {
            $sql = "SELECT 
                    solicitante,
                    COUNT(*) as total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id
                AND solicitante IS NOT NULL
                AND solicitante != ''";

            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $sql .= " AND YEAR(data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $sql .= " AND MONTH(data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            if ($setorId) {
                $sql .= " AND setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY solicitante
                  ORDER BY total DESC
                  LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            $colors = [
                'rgba(255, 99, 132, 0.7)',   // Vermelho
                'rgba(54, 162, 235, 0.7)',   // Azul
                'rgba(255, 206, 86, 0.7)',   // Amarelo
                'rgba(75, 192, 192, 0.7)',   // Verde
                'rgba(153, 102, 255, 0.7)',  // Roxo
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)',  // Cinza
                'rgba(83, 123, 196, 0.7)',   // Azul escuro
                'rgba(245, 130, 49, 0.7)',   // Laranja escuro
                'rgba(22, 160, 133, 0.7)'    // Verde escuro
            ];

            foreach ($result as $index => $row) {
                $labels[] = $row['solicitante'];
                $data[] = (int)$row['total'];
                $backgroundColor[] = $colors[$index % count($colors)];
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por solicitante: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém estatísticas gera  is para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtro
     * @param int|null $mes Mês para filtro (opcional)
     * @param int|null $setorId ID do setor para filtro (opcional)
     * @param string|null $condicaoAdicional Condição SQL adicional (opcional)
     * @param array|null $paramsAdicionais Parâmetros adicionais para a condição (opcional)
     * @return array Estatísticas gerais
     */
    public function getEstatisticasGerais($empresaId, $ano, $mes = null, $setorId = null, $condicaoAdicional = null, $paramsAdicionais = null)
    {
        try {
            // Se for fornecida uma condição adicional, use-a
            if ($condicaoAdicional && $paramsAdicionais) {
                $condicao = $condicaoAdicional;
                $params = $paramsAdicionais;
            } else {
                // Caso contrário, use a condição padrão
                $condicao = "empresa_id = :empresa_id AND YEAR(data_solicitacao) = :ano";
                $params = [
                    'empresa_id' => $empresaId,
                    'ano' => $ano
                ];

                if ($mes !== null) {
                    $condicao .= " AND MONTH(data_solicitacao) = :mes";
                    $params['mes'] = $mes;
                }

                if ($setorId !== null) {
                    $condicao .= " AND setor_id = :setor_id";
                    $params['setor_id'] = $setorId;
                }
            }

            // Calcula o período para média diária
            $diasPeriodo = 30; // Padrão: 30 dias

            if (isset($params['data_inicio']) && isset($params['data_fim'])) {
                // Extrai as datas dos parâmetros
                $dataInicio = new DateTime(substr($params['data_inicio'], 0, 10));
                $dataFim = new DateTime(substr($params['data_fim'], 0, 10));
                $diferenca = $dataInicio->diff($dataFim);
                $diasPeriodo = $diferenca->days + 1; // +1 para incluir o dia final
            } else if (isset($params['ano']) && isset($params['mes'])) {
                // Calcula o número de dias no mês
                $diasPeriodo = cal_days_in_month(CAL_GREGORIAN, $params['mes'], $params['ano']);
            } else if (isset($params['ano'])) {
                // Se é o ano atual, conta apenas os dias decorridos até hoje
                if ($params['ano'] == date('Y')) {
                    $diasPeriodo = date('z') + 1; // dias decorridos no ano + hoje
                } else {
                    // Um ano tem 365 dias (ou 366 em anos bissextos)
                    $diasPeriodo = (date('L', strtotime($params['ano'] . '-01-01')) == 1) ? 366 : 365;
                }
            }

            // Total de chamados
            $sqlTotal = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao";
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados concluídos (status_id = 4)
            $sqlConcluidos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 4";
            $stmtConcluidos = $this->db->prepare($sqlConcluidos);
            $stmtConcluidos->execute($params);
            $concluidos = $stmtConcluidos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados em andamento (status_id = 2)
            $sqlEmAndamento = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 2";
            $stmtEmAndamento = $this->db->prepare($sqlEmAndamento);
            $stmtEmAndamento->execute($params);
            $emAndamento = $stmtEmAndamento->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados abertos (status_id = 1)
            $sqlAbertos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 1";
            $stmtAbertos = $this->db->prepare($sqlAbertos);
            $stmtAbertos->execute($params);
            $abertos = $stmtAbertos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tempo médio de atendimento (em horas)
            $sqlTempoMedio = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, 
                    CASE 
                        WHEN data_conclusao IS NOT NULL THEN data_conclusao 
                        ELSE NOW() 
                    END)) as tempo_medio
                  FROM {$this->table} 
                  WHERE $condicao";
            $stmtTempoMedio = $this->db->prepare($sqlTempoMedio);
            $stmtTempoMedio->execute($params);
            $tempoMedio = $stmtTempoMedio->fetch(PDO::FETCH_ASSOC)['tempo_medio'] ?? 0;

            // Taxa de conclusão
            $taxaConclusao = $total > 0 ? ($concluidos / $total) * 100 : 0;

            return [
                'total' => $total,
                'concluidos' => $concluidos,
                'em_andamento' => $emAndamento,
                'abertos' => $abertos,
                'tempo_medio' => round($tempoMedio, 1),
                'taxa_conclusao' => round($taxaConclusao, 1),
                'dias_periodo' => $diasPeriodo
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas gerais: ' . $e->getMessage());
            return [
                'total' => 0,
                'concluidos' => 0,
                'em_andamento' => 0,
                'abertos' => 0,
                'tempo_medio' => 0,
                'taxa_conclusao' => 0,
                'dias_periodo' => 30
            ];
        }
    }

    /**
     * Obtém dados de taxa de resolução para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getTaxaResolucaoRelatorio($empresaId, $ano = null, $mes = null, $setorId = null)
    {
        try {
            // Log para depuração
            error_log("Obtendo taxa de resolução para empresa $empresaId");

            // Condição base
            $condicao = "empresa_id = :empresa_id";
            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $condicao .= " AND YEAR(data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $condicao .= " AND MONTH(data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            if ($setorId) {
                $condicao .= " AND setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            // Total de chamados
            $sqlTotal = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao";
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados concluídos (status_id = 4)
            $sqlConcluidos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 4";
            $stmtConcluidos = $this->db->prepare($sqlConcluidos);
            $stmtConcluidos->execute($params);
            $concluidos = $stmtConcluidos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados em andamento (status_id = 2)
            $sqlEmAndamento = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 2";
            $stmtEmAndamento = $this->db->prepare($sqlEmAndamento);
            $stmtEmAndamento->execute($params);
            $emAndamento = $stmtEmAndamento->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados abertos (status_id = 1)
            $sqlAbertos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 1";
            $stmtAbertos = $this->db->prepare($sqlAbertos);
            $stmtAbertos->execute($params);
            $abertos = $stmtAbertos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados cancelados (status_id = 5) ou outros status
            $sqlOutros = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id NOT IN (1, 2, 4)";
            $stmtOutros = $this->db->prepare($sqlOutros);
            $stmtOutros->execute($params);
            $outros = $stmtOutros->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Calcula as porcentagens
            $porcentagemConcluidos = $total > 0 ? ($concluidos / $total) * 100 : 0;
            $porcentagemEmAndamento = $total > 0 ? ($emAndamento / $total) * 100 : 0;
            $porcentagemAbertos = $total > 0 ? ($abertos / $total) * 100 : 0;
            $porcentagemOutros = $total > 0 ? ($outros / $total) * 100 : 0;

            // Prepara os dados para o gráfico
            $result = [
                [
                    'status' => 'Concluídos',
                    'total' => $concluidos,
                    'porcentagem' => round($porcentagemConcluidos, 1)
                ],
                [
                    'status' => 'Em Andamento',
                    'total' => $emAndamento,
                    'porcentagem' => round($porcentagemEmAndamento, 1)
                ],
                [
                    'status' => 'Abertos',
                    'total' => $abertos,
                    'porcentagem' => round($porcentagemAbertos, 1)
                ]
            ];

            // Adiciona "Outros" apenas se houver chamados nessa categoria
            if ($outros > 0) {
                $result[] = [
                    'status' => 'Outros',
                    'total' => $outros,
                    'porcentagem' => round($porcentagemOutros, 1)
                ];
            }

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [];

            $colors = [
                'Concluídos' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'Em Andamento' => 'rgba(255, 206, 86, 0.7)',  // Amarelo
                'Abertos' => 'rgba(255, 99, 132, 0.7)',       // Vermelho
                'Outros' => 'rgba(153, 102, 255, 0.7)'        // Roxo
            ];

            foreach ($result as $row) {
                $labels[] = $row['status'] . ' (' . $row['total'] . ')';
                $data[] = $row['porcentagem'];
                $backgroundColor[] = $colors[$row['status']] ?? 'rgba(201, 203, 207, 0.7)';
            }

            // Adiciona informações adicionais
            $taxaResolucao = $total > 0 ? ($concluidos / $total) * 100 : 0;

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result,
                'total' => $total,
                'concluidos' => $concluidos,
                'taxa_resolucao' => round($taxaResolucao, 1)
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter taxa de resolução: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => [],
                'total' => 0,
                'concluidos' => 0,
                'taxa_resolucao' => 0
            ];
        }
    }


    /**
     * Obtém dados de chamados por dia da semana para relatórios
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar (opcional)
     * @param int $mes Mês para filtrar (opcional)
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getChamadosPorDiaSemanaRelatorio($empresaId, $ano = null, $mes = null, $setorId = null)
    {
        try {
            // Log para depuração
            error_log("Obtendo chamados por dia da semana para empresa $empresaId");

            // Usamos DAYNAME para obter o nome do dia em inglês
            $sql = "SELECT 
                    DAYNAME(data_solicitacao) as dia_semana,
                    COUNT(*) as total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id";

            $params = ['empresa_id' => $empresaId];

            if ($ano) {
                $sql .= " AND YEAR(data_solicitacao) = :ano";
                $params['ano'] = $ano;
            }

            if ($mes) {
                $sql .= " AND MONTH(data_solicitacao) = :mes";
                $params['mes'] = $mes;
            }

            if ($setorId) {
                $sql .= " AND setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY dia_semana
                  ORDER BY FIELD(dia_semana, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')";

            error_log("SQL: $sql");
            error_log("Params: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Resultado bruto: " . print_r($result, true));

            // Mapeia os dias da semana em inglês para português
            $diasSemanaMap = [
                'Sunday' => 'Domingo',
                'Monday' => 'Segunda-feira',
                'Tuesday' => 'Terça-feira',
                'Wednesday' => 'Quarta-feira',
                'Thursday' => 'Quinta-feira',
                'Friday' => 'Sexta-feira',
                'Saturday' => 'Sábado'
            ];

            // Inicializa todos os dias com zero
            $dadosFormatados = [
                'Domingo' => 0,
                'Segunda-feira' => 0,
                'Terça-feira' => 0,
                'Quarta-feira' => 0,
                'Quinta-feira' => 0,
                'Sexta-feira' => 0,
                'Sábado' => 0
            ];

            // Preenche com os dados reais
            foreach ($result as $row) {
                $diaSemana = $row['dia_semana'];
                $diaSemanaPortugues = $diasSemanaMap[$diaSemana] ?? $diaSemana;
                $dadosFormatados[$diaSemanaPortugues] = (int)$row['total'];
            }

            error_log("Dados formatados: " . print_r($dadosFormatados, true));

            // Formata os dados para o gráfico
            $labels = array_keys($dadosFormatados);
            $data = array_values($dadosFormatados);

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => [
                    'rgba(54, 162, 235, 0.7)',   // Azul - Domingo
                    'rgba(255, 99, 132, 0.7)',   // Vermelho - Segunda
                    'rgba(255, 206, 86, 0.7)',   // Amarelo - Terça
                    'rgba(75, 192, 192, 0.7)',   // Verde - Quarta
                    'rgba(153, 102, 255, 0.7)',  // Roxo - Quinta
                    'rgba(255, 159, 64, 0.7)',   // Laranja - Sexta
                    'rgba(199, 199, 199, 0.7)'   // Cinza - Sábado
                ],
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por dia da semana: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    /**
     * Obtém dados de evolução mensal de chamados por status
     * 
     * @param int $empresaId ID da empresa
     * @param int $ano Ano para filtrar
     * @param int $setorId ID do setor para filtrar (opcional)
     * @return array Dados para gráfico
     */
    public function getEvolucaoMensalPorStatus($empresaId, $ano, $setorId = null)
    {
        try {
            $sql = "SELECT 
                    MONTH(c.data_solicitacao) as mes,
                    s.id as status_id,
                    s.nome as status_nome,
                    COUNT(*) as total
                FROM {$this->table} c
                JOIN status_chamados s ON c.status_id = s.id
                WHERE c.empresa_id = :empresa_id
                AND YEAR(c.data_solicitacao) = :ano";

            $params = [
                'empresa_id' => $empresaId,
                'ano' => $ano
            ];

            if ($setorId) {
                $sql .= " AND c.setor_id = :setor_id";
                $params['setor_id'] = $setorId;
            }

            $sql .= " GROUP BY MONTH(c.data_solicitacao), s.id, s.nome
                  ORDER BY mes, s.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtém todos os status
            $sqlStatus = "SELECT id, nome FROM status_chamados ORDER BY id";
            $stmtStatus = $this->db->prepare($sqlStatus);
            $stmtStatus->execute();
            $statusList = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

            // Mapeia os meses
            $meses = [
                1 => 'Jan',
                2 => 'Fev',
                3 => 'Mar',
                4 => 'Abr',
                5 => 'Mai',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ago',
                9 => 'Set',
                10 => 'Out',
                11 => 'Nov',
                12 => 'Dez'
            ];

            // Inicializa os dados
            $labels = array_values($meses);
            $datasets = [];

            // Cores para os status
            $statusColors = [
                'aberto' => ['rgba(255, 99, 132, 0.7)', 'rgba(255, 99, 132, 1)'],       // Vermelho
                'andamento' => ['rgba(255, 206, 86, 0.7)', 'rgba(255, 206, 86, 1)'],    // Amarelo
                'atendimento' => ['rgba(255, 206, 86, 0.7)', 'rgba(255, 206, 86, 1)'],  // Amarelo
                'concluído' => ['rgba(75, 192, 192, 0.7)', 'rgba(75, 192, 192, 1)'],    // Verde
                'resolvido' => ['rgba(75, 192, 192, 0.7)', 'rgba(75, 192, 192, 1)'],    // Verde
                'cancelado' => ['rgba(153, 102, 255, 0.7)', 'rgba(153, 102, 255, 1)'],  // Roxo
                'pendente' => ['rgba(54, 162, 235, 0.7)', 'rgba(54, 162, 235, 1)'],     // Azul
            ];

            // Cores alternativas
            $alternativeColors = [
                ['rgba(255, 159, 64, 0.7)', 'rgba(255, 159, 64, 1)'],   // Laranja
                ['rgba(199, 199, 199, 0.7)', 'rgba(199, 199, 199, 1)'], // Cinza
                ['rgba(83, 123, 196, 0.7)', 'rgba(83, 123, 196, 1)'],   // Azul escuro
                ['rgba(245, 130, 49, 0.7)', 'rgba(245, 130, 49, 1)'],   // Laranja escuro
                ['rgba(22, 160, 133, 0.7)', 'rgba(22, 160, 133, 1)']    // Verde escuro
            ];

            $colorIndex = 0;

            // Prepara os datasets para cada status
            foreach ($statusList as $status) {
                // Inicializa dados para todos os meses com zero
                $dadosMensais = array_fill(1, 12, 0);

                // Determina a cor com base no nome do status
                $statusNome = strtolower($status['nome']);
                $color = null;

                foreach ($statusColors as $keyword => $statusColor) {
                    if (strpos($statusNome, $keyword) !== false) {
                        $color = $statusColor;
                        break;
                    }
                }

                // Se não encontrou uma cor específica, usa uma das alternativas
                if (!$color) {
                    $color = $alternativeColors[$colorIndex % count($alternativeColors)];
                    $colorIndex++;
                }

                $datasets[] = [
                    'label' => $status['nome'],
                    'data' => $dadosMensais,
                    'backgroundColor' => $color[0],
                    'borderColor' => $color[1],
                    'borderWidth' => 1,
                    'status_id' => $status['id']
                ];
            }

            // Preenche os dados reais
            foreach ($result as $row) {
                $mes = (int)$row['mes'];
                $statusId = $row['status_id'];
                $total = (int)$row['total'];

                // Encontra o dataset correspondente
                foreach ($datasets as &$dataset) {
                    if ($dataset['status_id'] == $statusId) {
                        $dataset['data'][$mes] = $total;
                        break;
                    }
                }
            }

            // Converte os dados para arrays simples (sem índices)
            foreach ($datasets as &$dataset) {
                $dataset['data'] = array_values($dataset['data']);
                unset($dataset['status_id']);
            }

            return [
                'labels' => $labels,
                'datasets' => $datasets,
                'ano' => $ano
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter evolução mensal por status: ' . $e->getMessage());
            return [
                'labels' => [],
                'datasets' => [],
                'ano' => $ano
            ];
        }
    }

    /**
     * Aplica filtros avançados à consulta SQL
     * 
     * @param string $condicao Condição SQL existente
     * @param array $params Parâmetros existentes
     * @param array $filtros Filtros adicionais
     * @return array Condição e parâmetros atualizados
     */
    private function aplicarFiltrosAvancados($condicao, $params, $filtros)
    {
        // Filtro por status
        if (!empty($filtros['status_id'])) {
            $condicao .= " AND status_id = :status_id";
            $params['status_id'] = $filtros['status_id'];
        }

        // Filtro por tipo de serviço
        if (!empty($filtros['tipo_servico'])) {
            $condicao .= " AND tipo_servico = :tipo_servico";
            $params['tipo_servico'] = $filtros['tipo_servico'];
        }

        // Filtro por solicitante
        if (!empty($filtros['solicitante'])) {
            $condicao .= " AND solicitante LIKE :solicitante";
            $params['solicitante'] = '%' . $filtros['solicitante'] . '%';
        }

        // Filtro por data de início
        if (!empty($filtros['data_inicio'])) {
            $condicao .= " AND data_solicitacao >= :data_inicio_completa";
            $params['data_inicio_completa'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        // Filtro por data de fim
        if (!empty($filtros['data_fim'])) {
            $condicao .= " AND data_solicitacao <= :data_fim_completa";
            $params['data_fim_completa'] = $filtros['data_fim'] . ' 23:59:59';
        }

        return ['condicao' => $condicao, 'params' => $params];
    }

    /**
     * Obtém dados de chamados por status para relatórios com filtros avançados
     * 
     * @param int $empresaId ID da empresa
     * @param array $filtros Filtros adicionais
     * @return array Dados para gráfico
     */
    public function getChamadosPorStatusRelatorioAvancado($empresaId, $filtros = [])
    {
        try {
            $condicao = "c.empresa_id = :empresa_id";
            $params = ['empresa_id' => $empresaId];

            // Aplica filtros básicos
            if (!empty($filtros['ano'])) {
                $condicao .= " AND YEAR(c.data_solicitacao) = :ano";
                $params['ano'] = $filtros['ano'];
            }

            if (!empty($filtros['mes'])) {
                $condicao .= " AND MONTH(c.data_solicitacao) = :mes";
                $params['mes'] = $filtros['mes'];
            }

            if (!empty($filtros['setor_id'])) {
                $condicao .= " AND c.setor_id = :setor_id";
                $params['setor_id'] = $filtros['setor_id'];
            }

            // Aplica filtros avançados (exceto status_id, pois estamos agrupando por status)
            $filtrosSemStatus = $filtros;
            unset($filtrosSemStatus['status_id']);
            $resultado = $this->aplicarFiltrosAvancados($condicao, $params, $filtrosSemStatus);
            $condicao = $resultado['condicao'];
            $params = $resultado['params'];

            $sql = "SELECT 
                s.id as status_id,
                s.nome as status_nome,
                COUNT(*) as total
            FROM {$this->table} c
            JOIN status_chamados s ON c.status_id = s.id
            WHERE $condicao
            GROUP BY s.id, s.nome
            ORDER BY s.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico (mesmo código que antes)
            $labels = [];
            $data = [];
            $backgroundColor = [];

            // Cores específicas para status comuns
            $statusColors = [
                'aberto' => 'rgba(255, 99, 132, 0.7)',       // Vermelho
                'andamento' => 'rgba(255, 206, 86, 0.7)',    // Amarelo
                'atendimento' => 'rgba(255, 206, 86, 0.7)',  // Amarelo
                'concluído' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'resolvido' => 'rgba(75, 192, 192, 0.7)',    // Verde
                'cancelado' => 'rgba(153, 102, 255, 0.7)',   // Roxo
                'pendente' => 'rgba(54, 162, 235, 0.7)',     // Azul
            ];

            // Cores alternativas para outros status
            $alternativeColors = [
                'rgba(255, 159, 64, 0.7)',   // Laranja
                'rgba(199, 199, 199, 0.7)',  // Cinza
                'rgba(83, 123, 196, 0.7)',   // Azul escuro
                'rgba(245, 130, 49, 0.7)',   // Laranja escuro
                'rgba(22, 160, 133, 0.7)'    // Verde escuro
            ];

            $colorIndex = 0;

            foreach ($result as $row) {
                $labels[] = $row['status_nome'];
                $data[] = (int)$row['total'];

                // Determina a cor com base no nome do status
                $color = null;
                $statusNome = strtolower($row['status_nome']);

                foreach ($statusColors as $keyword => $statusColor) {
                    if (strpos($statusNome, $keyword) !== false) {
                        $color = $statusColor;
                        break;
                    }
                }

                // Se não encontrou uma cor específica, usa uma das alternativas
                if (!$color) {
                    $color = $alternativeColors[$colorIndex % count($alternativeColors)];
                    $colorIndex++;
                }

                $backgroundColor[] = $color;
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por status: ' . $e->getMessage());

            // Retorna um array vazio em caso de erro
            return [
                'labels' => [],
                'data' => [],
                'backgroundColor' => [],
                'raw' => []
            ];
        }
    }

    // Implemente os outros métodos avançados de forma similar:
    // - getChamadosPorMesRelatorioAvancado
    // - getTempoMedioAtendimentoAvancado
    // - getChamadosPorSetorRelatorioAvancado
    // - getChamadosPorTipoServicoRelatorioAvancado
    // - getTaxaResolucaoRelatorioAvancado
    // - getChamadosPorDiaSemanaRelatorioAvancado
    // - getEvolucaoMensalPorStatusAvancado
    // - getEstatisticasGeraisAvancado

    // Exemplo para o método de estatísticas gerais:
    /**
     * Obtém estatísticas gerais para relatórios com filtros avançados
     * 
     * @param int $empresaId ID da empresa
     * @param array $filtros Filtros adicionais
     * @return array Estatísticas gerais
     */
    public function getEstatisticasGeraisAvancado($empresaId, $filtros = [])
    {
        try {
            // Condição base
            $condicao = "empresa_id = :empresa_id";
            $params = ['empresa_id' => $empresaId];

            // Aplica filtros básicos
            if (!empty($filtros['ano'])) {
                $condicao .= " AND YEAR(data_solicitacao) = :ano";
                $params['ano'] = $filtros['ano'];
            }

            if (!empty($filtros['mes'])) {
                $condicao .= " AND MONTH(data_solicitacao) = :mes";
                $params['mes'] = $filtros['mes'];
            }

            if (!empty($filtros['setor_id'])) {
                $condicao .= " AND setor_id = :setor_id";
                $params['setor_id'] = $filtros['setor_id'];
            }

            // Aplica filtros avançados
            $resultado = $this->aplicarFiltrosAvancados($condicao, $params, $filtros);
            $condicao = $resultado['condicao'];
            $params = $resultado['params'];

            // Calcula o período para média diária
            $diasPeriodo = 30; // Padrão: 30 dias

            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                // Calcula o número de dias entre as datas
                $dataInicio = new DateTime($filtros['data_inicio']);
                $dataFim = new DateTime($filtros['data_fim']);
                $diferenca = $dataInicio->diff($dataFim);
                $diasPeriodo = $diferenca->days + 1; // +1 para incluir o dia final
            } else if (!empty($filtros['ano']) && !empty($filtros['mes'])) {
                // Calcula o número de dias no mês
                $diasPeriodo = cal_days_in_month(CAL_GREGORIAN, $filtros['mes'], $filtros['ano']);
            } else if (!empty($filtros['ano'])) {
                // Se é o ano atual, conta apenas os dias decorridos até hoje
                if ($filtros['ano'] == date('Y')) {
                    $diasPeriodo = date('z') + 1; // dias decorridos no ano + hoje
                } else {
                    // Um ano tem 365 dias (ou 366 em anos bissextos)
                    $diasPeriodo = (date('L', strtotime($filtros['ano'] . '-01-01')) == 1) ? 366 : 365;
                }
            }

            // Total de chamados
            $sqlTotal = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao";
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados concluídos (status_id = 4)
            $sqlConcluidos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 4";
            $stmtConcluidos = $this->db->prepare($sqlConcluidos);
            $stmtConcluidos->execute($params);
            $concluidos = $stmtConcluidos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados em andamento (status_id = 2)
            $sqlEmAndamento = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 2";
            $stmtEmAndamento = $this->db->prepare($sqlEmAndamento);
            $stmtEmAndamento->execute($params);
            $emAndamento = $stmtEmAndamento->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Chamados abertos (status_id = 1)
            $sqlAbertos = "SELECT COUNT(*) as total FROM {$this->table} WHERE $condicao AND status_id = 1";
            $stmtAbertos = $this->db->prepare($sqlAbertos);
            $stmtAbertos->execute($params);
            $abertos = $stmtAbertos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tempo médio de atendimento (em horas)
            $sqlTempoMedio = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, 
                    CASE 
                        WHEN data_conclusao IS NOT NULL THEN data_conclusao 
                        ELSE NOW() 
                    END)) as tempo_medio
                  FROM {$this->table} 
                  WHERE $condicao";
            $stmtTempoMedio = $this->db->prepare($sqlTempoMedio);
            $stmtTempoMedio->execute($params);
            $tempoMedio = $stmtTempoMedio->fetch(PDO::FETCH_ASSOC)['tempo_medio'] ?? 0;

            // Taxa de conclusão
            $taxaConclusao = $total > 0 ? ($concluidos / $total) * 100 : 0;

            // Log para depuração
            error_log("Estatísticas: Total=$total, Dias=$diasPeriodo, Média=" . ($total / max(1, $diasPeriodo)));

            return [
                'total' => $total,
                'concluidos' => $concluidos,
                'em_andamento' => $emAndamento,
                'abertos' => $abertos,
                'tempo_medio' => round($tempoMedio, 1),
                'taxa_conclusao' => round($taxaConclusao, 1),
                'dias_periodo' => $diasPeriodo
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas gerais: ' . $e->getMessage());
            return [
                'total' => 0,
                'concluidos' => 0,
                'em_andamento' => 0,
                'abertos' => 0,
                'tempo_medio' => 0,
                'taxa_conclusao' => 0,
                'dias_periodo' => 30
            ];
        }
    }

    /**
     * Obtém dados de chamados por mês para relatórios com filtros avançados
     * 
     * @param int $empresaId ID da empresa
     * @param array $filtros Filtros adicionais
     * @return array Dados para gráfico
     */
    public function getChamadosPorMesRelatorioAvancado($empresaId, $filtros = [])
    {
        try {
            // Se não for informado o ano, usa o ano atual
            $ano = !empty($filtros['ano']) ? $filtros['ano'] : date('Y');

            $condicao = "empresa_id = :empresa_id AND YEAR(data_solicitacao) = :ano";
            $params = [
                'empresa_id' => $empresaId,
                'ano' => $ano
            ];

            // Aplica filtros adicionais (exceto mês, pois estamos agrupando por mês)
            $filtrosSemMes = $filtros;
            unset($filtrosSemMes['mes']);
            unset($filtrosSemMes['ano']); // Já aplicamos o ano acima

            $resultado = $this->aplicarFiltrosAvancados($condicao, $params, $filtrosSemMes);
            $condicao = $resultado['condicao'];
            $params = $resultado['params'];

            $sql = "SELECT 
                MONTH(data_solicitacao) as mes, 
                COUNT(*) as total 
            FROM {$this->table} 
            WHERE $condicao
            GROUP BY MONTH(data_solicitacao)
            ORDER BY mes";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];

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

            // Inicializa todos os meses com zero
            foreach ($meses as $numMes => $nomeMes) {
                $labels[$numMes] = $nomeMes;
                $data[$numMes] = 0;
            }

            // Preenche com os dados reais
            foreach ($result as $row) {
                $data[$row['mes']] = (int)$row['total'];
            }

            return [
                'labels' => array_values($labels),
                'data' => array_values($data),
                'ano' => $ano,
                'raw' => $result
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter chamados por mês: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => [],
                'ano' => $ano,
                'raw' => []
            ];
        }
    }
}
