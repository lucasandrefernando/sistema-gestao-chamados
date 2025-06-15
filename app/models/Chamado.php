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
            // Verifica se a tabela existe
            $this->db->query("SELECT 1 FROM {$this->table} LIMIT 1");

            // Total de chamados
            $total = $this->count('empresa_id = :empresa_id', ['empresa_id' => $empresaId]);

            // Chamados por status
            $abertos = $this->count('empresa_id = :empresa_id AND status = :status', [
                'empresa_id' => $empresaId,
                'status' => 'aberto'
            ]);

            $emAndamento = $this->count('empresa_id = :empresa_id AND status = :status', [
                'empresa_id' => $empresaId,
                'status' => 'em_andamento'
            ]);

            $concluidos = $this->count('empresa_id = :empresa_id AND status = :status', [
                'empresa_id' => $empresaId,
                'status' => 'concluido'
            ]);

            $cancelados = $this->count('empresa_id = :empresa_id AND status = :status', [
                'empresa_id' => $empresaId,
                'status' => 'cancelado'
            ]);

            // Últimos chamados
            $sql = "SELECT c.*, u.nome as solicitante_nome, s.nome as setor_nome 
                    FROM {$this->table} c
                    LEFT JOIN usuarios u ON c.solicitante_id = u.id
                    LEFT JOIN setores s ON c.setor_id = s.id
                    WHERE c.empresa_id = :empresa_id
                    ORDER BY c.data_abertura DESC
                    LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $ultimosChamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total' => $total,
                'abertos' => $abertos,
                'em_andamento' => $emAndamento,
                'concluidos' => $concluidos,
                'cancelados' => $cancelados,
                'ultimos_chamados' => $ultimosChamados
            ];
        } catch (Exception $e) {
            // Se a tabela não existir ou ocorrer outro erro, retorna estatísticas vazias
            return [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0,
                'cancelados' => 0,
                'ultimos_chamados' => []
            ];
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
            $sql = "SELECT status, COUNT(*) as total 
                    FROM {$this->table} 
                    WHERE empresa_id = :empresa_id 
                    GROUP BY status";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];
            $backgroundColor = [
                'aberto' => 'rgba(255, 99, 132, 0.7)',
                'em_andamento' => 'rgba(54, 162, 235, 0.7)',
                'concluido' => 'rgba(75, 192, 192, 0.7)',
                'cancelado' => 'rgba(201, 203, 207, 0.7)'
            ];
            $colors = [];

            foreach ($result as $row) {
                $status = $row['status'];
                $labels[] = $this->formatarStatus($status);
                $data[] = (int)$row['total'];
                $colors[] = $backgroundColor[$status] ?? 'rgba(153, 102, 255, 0.7)';
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $colors
            ];
        } catch (Exception $e) {
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
     * Obtém dados de chamados por mês para gráficos
     * 
     * @param int $empresaId ID da empresa
     * @return array Dados para gráfico
     */
    public function getChamadosPorMes($empresaId)
    {
        try {
            $sql = "SELECT 
                        MONTH(data_abertura) as mes, 
                        YEAR(data_abertura) as ano,
                        COUNT(*) as total 
                    FROM {$this->table} 
                    WHERE empresa_id = :empresa_id 
                    AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY YEAR(data_abertura), MONTH(data_abertura)
                    ORDER BY ano, mes";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formata os dados para o gráfico
            $labels = [];
            $data = [];

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

            foreach ($result as $row) {
                $labels[] = $meses[$row['mes']] . '/' . substr($row['ano'], 2);
                $data[] = (int)$row['total'];
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'labels' => [],
                'data' => []
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
            $sql = "SELECT c.id, c.titulo, c.status, c.data_abertura, 
                        u.nome as solicitante_nome, 
                        'chamado' as tipo,
                        c.data_abertura as data
                    FROM {$this->table} c
                    LEFT JOIN usuarios u ON c.solicitante_id = u.id
                    WHERE c.empresa_id = :empresa_id
                    
                    UNION
                    
                    SELECT c.id, c.titulo, c.status, cc.data_criacao, 
                        u.nome as usuario_nome,
                        'comentario' as tipo,
                        cc.data_criacao as data
                    FROM chamados_comentarios cc
                    JOIN {$this->table} c ON cc.chamado_id = c.id
                    LEFT JOIN usuarios u ON cc.usuario_id = u.id
                    WHERE c.empresa_id = :empresa_id
                    
                    ORDER BY data DESC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['empresa_id' => $empresaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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
     * Obtém os tipos de serviço únicos
     * 
     * @param int $empresaId ID da empresa
     * @return array Tipos de serviço
     */
    public function getTiposServico($empresaId)
    {
        try {
            $sql = "SELECT DISTINCT tipo_servico 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND tipo_servico IS NOT NULL
                ORDER BY tipo_servico ASC";

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
     * Obtém os solicitantes únicos
     * 
     * @param int $empresaId ID da empresa
     * @return array Solicitantes
     */
    public function getSolicitantes($empresaId)
    {
        try {
            $sql = "SELECT DISTINCT solicitante 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                ORDER BY solicitante ASC";

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
}
