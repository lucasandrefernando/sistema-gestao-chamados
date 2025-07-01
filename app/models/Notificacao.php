<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de notificações
 * 
 * Este modelo gerencia todas as operações relacionadas a notificações,
 * incluindo criação, busca, contagem, marcação como lida e exclusão.
 * 
 * @package Sistema de Gestão de Chamados
 * @version 2.0.0
 */
class Notificacao extends Model
{
    /**
     * Nome da tabela no banco de dados
     * @var string
     */
    protected $table = 'notificacoes';

    /**
     * Construtor
     * Inicializa o modelo
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria uma nova notificação
     * 
     * @param array $dados Dados da notificação
     * @return int|bool ID da notificação criada ou false em caso de erro
     */
    public function criarNotificacao($dados)
    {
        // Validar dados mínimos
        if (!isset($dados['usuario_id']) || !isset($dados['tipo']) || !isset($dados['titulo'])) {
            error_log("Dados insuficientes para criar notificação: " . json_encode($dados));
            return false;
        }

        // Definir data de criação
        $dados['data_criacao'] = date('Y-m-d H:i:s');

        // Garantir que o campo lida seja 0 (não lida)
        $dados['lida'] = 0;

        try {
            // Criar notificação usando SQL direto para ter mais controle
            $campos = array_keys($dados);
            $placeholders = array_map(function ($campo) {
                return ":$campo";
            }, $campos);

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $campos) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->db->prepare($sql);

            foreach ($dados as $campo => $valor) {
                $stmt->bindValue(":$campo", $valor);
            }

            $stmt->execute();
            $id = $this->db->lastInsertId();

            error_log("Notificação criada com sucesso. ID: $id");
            return $id;
        } catch (Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca notificações não lidas de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @param int $limite Limite de notificações a retornar
     * @return array Lista de notificações
     */
    public function buscarNotificacoesNaoLidas($usuarioId, $limite = 5)
    {
        return $this->findAll(
            'usuario_id = :usuario_id AND lida = 0',
            ['usuario_id' => $usuarioId],
            'data_criacao DESC',
            $limite
        );
    }

    /**
     * Conta notificações não lidas de um usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return int Número de notificações não lidas
     */
    public function contarNotificacoesNaoLidas($usuarioId)
    {
        return $this->count(
            'usuario_id = :usuario_id AND lida = 0',
            ['usuario_id' => $usuarioId]
        );
    }

    /**
     * Marca uma notificação como lida
     * 
     * @param int $id ID da notificação
     * @return bool Sucesso ou falha
     */
    public function marcarComoLida($id)
    {
        return $this->update($id, [
            'lida' => 1,
            'data_leitura' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marca todas as notificações de um usuário como lidas
     * 
     * @param int $usuarioId ID do usuário
     * @return bool Sucesso ou falha
     */
    public function marcarTodasComoLidas($usuarioId)
    {
        $sql = "UPDATE {$this->table} SET lida = 1, data_leitura = NOW() 
                WHERE usuario_id = :usuario_id AND lida = 0";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['usuario_id' => $usuarioId]);
    }

    /**
     * Cria notificações para todos os usuários de um setor
     * 
     * @param int $setorId ID do setor
     * @param array $dados Dados da notificação
     * @return bool Sucesso ou falha
     */
    public function notificarSetor($setorId, $dados)
    {
        // Buscar usuários do setor usando a tabela usuarios_setores
        $sql = "SELECT u.id FROM usuarios u 
            INNER JOIN usuarios_setores us ON u.id = us.usuario_id 
            WHERE us.setor_id = :setor_id AND u.ativo = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['setor_id' => $setorId]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($usuarios)) {
            // Registrar que não foram encontrados usuários para este setor
            error_log("Nenhum usuário encontrado para o setor ID: $setorId");
            return false;
        }

        // Criar notificação para cada usuário
        $sucesso = true;
        foreach ($usuarios as $usuarioId) {
            $dados['usuario_id'] = $usuarioId;
            $resultado = $this->criarNotificacao($dados);
            if (!$resultado) {
                error_log("Falha ao criar notificação para usuário ID: $usuarioId");
                $sucesso = false;
            }
        }

        return $sucesso;
    }

    /**
     * Cria notificações para todos os usuários de um setor, exceto um usuário específico
     * 
     * @param int $setorId ID do setor
     * @param int $excluirUsuarioId ID do usuário a ser excluído da notificação
     * @param array $dados Dados da notificação
     * @return bool Sucesso ou falha
     */
    public function notificarSetorExcetoUsuario($setorId, $excluirUsuarioId, $dados)
    {
        // Buscar usuários do setor usando a tabela usuarios_setores
        $sql = "SELECT u.id FROM usuarios u 
            INNER JOIN usuarios_setores us ON u.id = us.usuario_id 
            WHERE us.setor_id = :setor_id AND u.ativo = 1 AND u.id != :excluir_usuario_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'setor_id' => $setorId,
            'excluir_usuario_id' => $excluirUsuarioId
        ]);

        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($usuarios)) {
            return false;
        }

        // Criar notificação para cada usuário
        $sucesso = true;
        foreach ($usuarios as $usuarioId) {
            $dados['usuario_id'] = $usuarioId;
            if (!$this->criarNotificacao($dados)) {
                $sucesso = false;
            }
        }

        return $sucesso;
    }

    /**
     * Cria notificações para usuários baseado no e-mail do setor
     * 
     * @param string $emailSetor E-mail do setor
     * @param array $dados Dados da notificação
     * @return bool Sucesso ou falha
     */
    public function notificarPorEmailSetor($emailSetor, $dados)
    {
        // Buscar ID do setor pelo e-mail
        $sql = "SELECT id FROM setores WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $emailSetor]);
        $setorId = $stmt->fetchColumn();

        if (!$setorId) {
            return false;
        }

        // Notificar o setor
        return $this->notificarSetor($setorId, $dados);
    }

    /**
     * Formata o tempo relativo para exibição
     * 
     * @param string $datetime Data e hora no formato Y-m-d H:i:s
     * @return string Tempo relativo formatado
     */
    public function formatarTempoRelativo($datetime)
    {
        if (empty($datetime)) {
            return 'Data desconhecida';
        }

        $now = new DateTime();
        $date = new DateTime($datetime);
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y . ' ' . ($diff->y > 1 ? 'anos' : 'ano') . ' atrás';
        }

        if ($diff->m > 0) {
            return $diff->m . ' ' . ($diff->m > 1 ? 'meses' : 'mês') . ' atrás';
        }

        if ($diff->d > 0) {
            if ($diff->d == 1) {
                return 'Ontem';
            }
            return $diff->d . ' dias atrás';
        }

        if ($diff->h > 0) {
            return $diff->h . ' ' . ($diff->h > 1 ? 'horas' : 'hora') . ' atrás';
        }

        if ($diff->i > 0) {
            return $diff->i . ' ' . ($diff->i > 1 ? 'minutos' : 'minuto') . ' atrás';
        }

        return 'Agora mesmo';
    }

    /**
     * Busca notificações formatadas para exibição
     * 
     * @param int $usuarioId ID do usuário
     * @param int $limite Limite de notificações a retornar
     * @return array Lista de notificações formatadas
     */
    public function buscarNotificacoesFormatadas($usuarioId, $limite = 5)
    {
        $notificacoes = $this->buscarNotificacoesNaoLidas($usuarioId, $limite);
        $formatadas = [];

        foreach ($notificacoes as $notificacao) {
            // Definir valores padrão para campos que podem estar ausentes
            $tipo = isset($notificacao['tipo']) ? $notificacao['tipo'] : 'geral';
            $titulo = isset($notificacao['titulo']) ? $notificacao['titulo'] : 'Notificação';
            $descricao = isset($notificacao['descricao']) ? $notificacao['descricao'] : '';
            $dataCriacao = isset($notificacao['data_criacao']) ? $notificacao['data_criacao'] : date('Y-m-d H:i:s');
            $referenciaId = isset($notificacao['referencia_id']) ? $notificacao['referencia_id'] : null;
            $referenciaTipo = isset($notificacao['referencia_tipo']) ? $notificacao['referencia_tipo'] : null;

            $icone = 'fas fa-bell';
            $cor = 'primary';

            // Definir ícone e cor com base no tipo
            switch ($tipo) {
                case 'novo_chamado':
                    $icone = 'fas fa-ticket-alt';
                    $cor = 'primary';
                    break;
                case 'chamado_concluido':
                    $icone = 'fas fa-check-circle';
                    $cor = 'success';
                    break;
                case 'chamado_pendente':
                    $icone = 'fas fa-clock';
                    $cor = 'warning';
                    break;
                case 'chamado_atualizado':
                    $icone = 'fas fa-sync-alt';
                    $cor = 'info';
                    break;
                case 'comentario_adicionado':
                    $icone = 'fas fa-comment';
                    $cor = 'info';
                    break;
                case 'chamado_transferido':
                    $icone = 'fas fa-exchange-alt';
                    $cor = 'primary';
                    break;
            }

            $formatadas[] = [
                'id' => $notificacao['id'],
                'tipo' => $tipo,
                'titulo' => $titulo,
                'descricao' => $descricao,
                'mensagem' => $descricao, // Adicionado para compatibilidade com a view
                'tempo' => $this->formatarTempoRelativo($dataCriacao),
                'data_criacao' => $dataCriacao, // Adicionado para compatibilidade com a view
                'icone' => $icone,
                'cor' => $cor,
                'referencia_id' => $referenciaId,
                'referencia_tipo' => $referenciaTipo
            ];
        }

        return $formatadas;
    }

    /**
     * Obtém a conexão com o banco de dados
     * 
     * @return PDO Conexão com o banco de dados
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Obtém o nome da tabela
     * 
     * @return string Nome da tabela
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Verifica se uma coluna existe na tabela
     * 
     * @param string $coluna Nome da coluna
     * @return bool True se a coluna existir, false caso contrário
     */
    public function colunaExiste($coluna)
    {
        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE :coluna";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['coluna' => $coluna]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar se coluna existe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona uma coluna à tabela se ela não existir
     * 
     * @param string $coluna Nome da coluna
     * @param string $definicao Definição da coluna (tipo, etc.)
     * @return bool True se a operação for bem-sucedida, false caso contrário
     */
    public function adicionarColunaSeNaoExistir($coluna, $definicao)
    {
        if ($this->colunaExiste($coluna)) {
            return true;
        }

        try {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN $coluna $definicao";
            $this->db->exec($sql);
            error_log("Coluna $coluna adicionada à tabela {$this->table}");
            return true;
        } catch (Exception $e) {
            error_log("Erro ao adicionar coluna $coluna: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica e corrige a estrutura da tabela de notificações
     * 
     * @return bool True se a estrutura estiver correta ou for corrigida, false caso contrário
     */
    public function verificarEstrutura()
    {
        try {
            // Verificar se a tabela existe
            $sql = "SHOW TABLES LIKE '{$this->table}'";
            $stmt = $this->db->query($sql);

            if ($stmt->rowCount() == 0) {
                // A tabela não existe, criar
                $sql = "CREATE TABLE {$this->table} (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    usuario_id INT(11) NOT NULL,
                    tipo VARCHAR(50) DEFAULT 'geral',
                    titulo VARCHAR(255) NOT NULL,
                    descricao TEXT,
                    referencia_id INT(11) DEFAULT NULL,
                    referencia_tipo VARCHAR(50) DEFAULT NULL,
                    lida TINYINT(1) NOT NULL DEFAULT 0,
                    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    data_leitura DATETIME DEFAULT NULL,
                    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    removido TINYINT(1) NOT NULL DEFAULT 0,
                    data_remocao DATETIME DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY usuario_id (usuario_id),
                    CONSTRAINT notificacoes_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

                $this->db->exec($sql);
                error_log("Tabela {$this->table} criada com sucesso");
                return true;
            }

            // Verificar e adicionar colunas necessárias
            $this->adicionarColunaSeNaoExistir('tipo', "VARCHAR(50) DEFAULT 'geral'");
            $this->adicionarColunaSeNaoExistir('titulo', "VARCHAR(255) NOT NULL DEFAULT 'Notificação'");
            $this->adicionarColunaSeNaoExistir('descricao', "TEXT");
            $this->adicionarColunaSeNaoExistir('referencia_id', "INT(11) DEFAULT NULL");
            $this->adicionarColunaSeNaoExistir('referencia_tipo', "VARCHAR(50) DEFAULT NULL");
            $this->adicionarColunaSeNaoExistir('lida', "TINYINT(1) NOT NULL DEFAULT 0");
            $this->adicionarColunaSeNaoExistir('data_criacao', "DATETIME DEFAULT CURRENT_TIMESTAMP");
            $this->adicionarColunaSeNaoExistir('data_leitura', "DATETIME DEFAULT NULL");
            $this->adicionarColunaSeNaoExistir('data_atualizacao', "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            $this->adicionarColunaSeNaoExistir('removido', "TINYINT(1) NOT NULL DEFAULT 0");
            $this->adicionarColunaSeNaoExistir('data_remocao', "DATETIME DEFAULT NULL");

            return true;
        } catch (Exception $e) {
            error_log("Erro ao verificar estrutura da tabela {$this->table}: " . $e->getMessage());
            return false;
        }
    }
}
