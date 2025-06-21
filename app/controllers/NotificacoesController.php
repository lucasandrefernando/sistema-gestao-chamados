<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Notificacao.php';

/**
 * Controlador para gerenciamento de notificações
 */
class NotificacoesController extends Controller
{
    private $notificacaoModel;

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

        // Inicializa o modelo
        $this->notificacaoModel = new Notificacao();
    }

    /**
     * Lista todas as notificações do usuário
     */
    public function index()
    {
        $usuarioId = get_user_id();

        // Buscar todas as notificações do usuário
        $notificacoes = $this->notificacaoModel->findAll(
            'usuario_id = :usuario_id',
            ['usuario_id' => $usuarioId],
            'data_criacao DESC'
        );

        // Formatar as notificações para exibição
        $notificacoesFormatadas = [];
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

            $notificacoesFormatadas[] = [
                'id' => $notificacao['id'],
                'tipo' => $notificacao['tipo'],
                'titulo' => $notificacao['titulo'],
                'descricao' => $notificacao['descricao'],
                'tempo' => $this->notificacaoModel->formatarTempoRelativo($notificacao['data_criacao']),
                'icone' => $icone,
                'cor' => $cor,
                'lida' => $notificacao['lida'],
                'referencia_id' => $notificacao['referencia_id'],
                'referencia_tipo' => $notificacao['referencia_tipo']
            ];
        }

        $this->render('notificacoes/index', [
            'notificacoes' => $notificacoesFormatadas,
            'total_nao_lidas' => $this->notificacaoModel->contarNotificacoesNaoLidas($usuarioId)
        ]);
    }

    /**
     * Marca uma notificação como lida
     */
    public function marcarLida($id)
    {
        $usuarioId = get_user_id();

        // Verificar se a notificação pertence ao usuário
        $notificacao = $this->notificacaoModel->findOne(
            'id = :id AND usuario_id = :usuario_id',
            ['id' => $id, 'usuario_id' => $usuarioId]
        );

        if (!$notificacao) {
            // Se for uma requisição AJAX
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
                exit;
            }

            set_flash_message('error', 'Notificação não encontrada.');
            redirect('notificacoes');
            exit;
        }

        // Marcar como lida
        $sucesso = $this->notificacaoModel->marcarComoLida($id);

        // Se for uma requisição AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $sucesso,
                'message' => $sucesso ? 'Notificação marcada como lida' : 'Erro ao marcar notificação como lida'
            ]);
            exit;
        }

        if ($sucesso) {
            set_flash_message('success', 'Notificação marcada como lida.');
        } else {
            set_flash_message('error', 'Erro ao marcar notificação como lida.');
        }

        redirect('notificacoes');
    }

    /**
     * Marca todas as notificações do usuário como lidas
     */
    public function marcarTodasLidas()
    {
        $usuarioId = get_user_id();

        // Marcar todas como lidas
        $sucesso = $this->notificacaoModel->marcarTodasComoLidas($usuarioId);

        // Se for uma requisição AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $sucesso,
                'message' => $sucesso ? 'Todas as notificações marcadas como lidas' : 'Erro ao marcar notificações como lidas'
            ]);
            exit;
        }

        if ($sucesso) {
            set_flash_message('success', 'Todas as notificações foram marcadas como lidas.');
        } else {
            set_flash_message('error', 'Erro ao marcar notificações como lidas.');
        }

        redirect('notificacoes');
    }

    /**
     * Busca notificações não lidas para exibição no header
     * Endpoint AJAX
     */
    public function buscarNaoLidas()
    {
        $usuarioId = get_user_id();

        // Buscar notificações não lidas
        $notificacoes = $this->notificacaoModel->buscarNotificacoesFormatadas($usuarioId);
        $total = $this->notificacaoModel->contarNotificacoesNaoLidas($usuarioId);

        // Retornar como JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notificacoes' => $notificacoes,
            'total' => $total
        ]);
        exit;
    }

    /**
     * Exclui uma notificação
     */
    public function excluir($id)
    {
        $usuarioId = get_user_id();

        // Verificar se a notificação pertence ao usuário
        $notificacao = $this->notificacaoModel->findOne(
            'id = :id AND usuario_id = :usuario_id',
            ['id' => $id, 'usuario_id' => $usuarioId]
        );

        if (!$notificacao) {
            // Se for uma requisição AJAX
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
                exit;
            }

            set_flash_message('error', 'Notificação não encontrada.');
            redirect('notificacoes');
            exit;
        }

        // Excluir notificação
        $sucesso = $this->notificacaoModel->delete($id);

        // Se for uma requisição AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $sucesso,
                'message' => $sucesso ? 'Notificação excluída' : 'Erro ao excluir notificação'
            ]);
            exit;
        }

        if ($sucesso) {
            set_flash_message('success', 'Notificação excluída com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao excluir notificação.');
        }

        redirect('notificacoes');
    }
}
