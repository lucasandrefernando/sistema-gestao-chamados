<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de notificações
 */
class Notificacao extends Model
{
    protected $table = 'notificacoes';

    /**
     * Construtor
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
            return false;
        }

        // Definir data de criação
        $dados['data_criacao'] = date('Y-m-d H:i:s');

        // Criar notificação
        return $this->create($dados);
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
        // Buscar usuários do setor
        $sql = "SELECT id FROM usuarios WHERE setor_id = :setor_id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['setor_id' => $setorId]);
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
            $icone = 'fas fa-bell';
            $cor = 'primary';

            // Definir ícone e cor com base no tipo
            switch ($notificacao['tipo']) {
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
            }

            $formatadas[] = [
                'id' => $notificacao['id'],
                'tipo' => $notificacao['tipo'],
                'titulo' => $notificacao['titulo'],
                'descricao' => $notificacao['descricao'],
                'tempo' => $this->formatarTempoRelativo($notificacao['data_criacao']),
                'icone' => $icone,
                'cor' => $cor,
                'referencia_id' => $notificacao['referencia_id'],
                'referencia_tipo' => $notificacao['referencia_tipo']
            ];
        }

        return $formatadas;
    }
}
