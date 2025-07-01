<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Notificacao.php';

/**
 * Controlador para gerenciamento de notificações
 * 
 * Este controlador gerencia todas as operações relacionadas a notificações,
 * incluindo listagem, marcação como lida, exclusão e diagnóstico.
 * 
 * @package Sistema de Gestão de Chamados
 * @version 2.0.0
 */
class NotificacoesController extends Controller
{
    /**
     * Modelo de notificações
     * @var Notificacao
     */
    private $notificacaoModel;

    /**
     * Construtor
     * Inicializa o controlador e verifica autenticação
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
     * Exibe a página principal de notificações
     */
    public function index()
    {
        $usuarioId = get_user_id();

        // Adicionar log para depuração
        error_log("Buscando notificações para o usuário ID: $usuarioId");

        // Buscar todas as notificações do usuário (não apenas as não lidas)
        $notificacoes = $this->notificacaoModel->findAll(
            'usuario_id = :usuario_id',
            ['usuario_id' => $usuarioId],
            'data_criacao DESC'
        );

        error_log("Total de notificações encontradas: " . count($notificacoes));

        // Formatar as notificações para exibição
        $notificacoesFormatadas = [];
        foreach ($notificacoes as $notificacao) {
            // Definir valores padrão para campos que podem estar ausentes
            $tipo = isset($notificacao['tipo']) ? $notificacao['tipo'] : 'geral';
            $titulo = isset($notificacao['titulo']) ? $notificacao['titulo'] : 'Notificação';
            $descricao = isset($notificacao['descricao']) ? $notificacao['descricao'] : '';
            $dataCriacao = isset($notificacao['data_criacao']) ? $notificacao['data_criacao'] : date('Y-m-d H:i:s');
            $lida = isset($notificacao['lida']) ? $notificacao['lida'] : 0;
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
                case 'teste':
                    $icone = 'fas fa-vial';
                    $cor = 'danger';
                    break;
            }

            $notificacoesFormatadas[] = [
                'id' => $notificacao['id'],
                'tipo' => $tipo,
                'titulo' => $titulo,
                'descricao' => $descricao,
                'mensagem' => $descricao, // Adicionado para compatibilidade com a view
                'tempo' => $this->notificacaoModel->formatarTempoRelativo($dataCriacao),
                'data_criacao' => $dataCriacao, // Adicionado para compatibilidade com a view
                'icone' => $icone,
                'cor' => $cor,
                'lida' => $lida,
                'referencia_id' => $referenciaId,
                'referencia_tipo' => $referenciaTipo
            ];
        }

        error_log("Notificações formatadas: " . json_encode($notificacoesFormatadas));

        $this->render('notificacoes/index', [
            'notificacoes' => $notificacoesFormatadas,
            'total_nao_lidas' => $this->notificacaoModel->contarNotificacoesNaoLidas($usuarioId)
        ]);
    }

    /**
     * Marca uma notificação como lida
     * 
     * @param int $id ID da notificação
     */
    public function marcarComoLida($id)
    {
        $usuarioId = get_user_id();

        // Log para depuração
        error_log("Tentativa de marcar notificação ID: $id como lida para usuário ID: $usuarioId");

        // Verificar se a notificação pertence ao usuário
        $notificacao = $this->notificacaoModel->findOne(
            'id = :id AND usuario_id = :usuario_id',
            ['id' => $id, 'usuario_id' => $usuarioId]
        );

        if (!$notificacao) {
            error_log("Notificação ID: $id não encontrada ou não pertence ao usuário ID: $usuarioId");

            // Se for uma requisição AJAX
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Notificação não encontrada']);
                return;
            }

            set_flash_message('error', 'Notificação não encontrada.');
            redirect('notificacoes');
            return;
        }

        // Marcar como lida
        $sucesso = $this->notificacaoModel->marcarComoLida($id);
        error_log("Resultado ao marcar notificação ID: $id como lida: " . ($sucesso ? 'Sucesso' : 'Falha'));

        // Se for uma requisição AJAX
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => $sucesso,
                'message' => $sucesso ? 'Notificação marcada como lida' : 'Erro ao marcar notificação como lida'
            ]);
            return;
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
    public function marcarTodasComoLidas()
    {
        $usuarioId = get_user_id();

        // Log para depuração
        error_log("Tentativa de marcar todas as notificações como lidas para usuário ID: $usuarioId");

        // Marcar todas como lidas
        $sucesso = $this->notificacaoModel->marcarTodasComoLidas($usuarioId);
        error_log("Resultado ao marcar todas as notificações como lidas: " . ($sucesso ? 'Sucesso' : 'Falha'));

        // Se for uma requisição AJAX
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => $sucesso,
                'message' => $sucesso ? 'Todas as notificações marcadas como lidas' : 'Erro ao marcar notificações como lidas'
            ]);
            return;
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

        // Log para depuração
        error_log("Buscando notificações não lidas para o usuário ID: $usuarioId");

        // Buscar notificações não lidas
        $notificacoes = $this->notificacaoModel->buscarNotificacoesFormatadas($usuarioId);
        $total = $this->notificacaoModel->contarNotificacoesNaoLidas($usuarioId);

        // Log do resultado
        error_log("Total de notificações não lidas: $total");

        // Retornar como JSON
        $this->jsonResponse([
            'success' => true,
            'notificacoes' => $notificacoes,
            'total' => $total
        ]);
    }

    /**
     * Exclui uma notificação
     * 
     * @param int $id ID da notificação
     */
    public function excluir($id)
    {
        $usuarioId = get_user_id();

        // Log para depuração
        error_log("Tentativa de excluir notificação ID: $id para usuário ID: $usuarioId");

        // Verificar se a notificação pertence ao usuário
        $notificacao = $this->notificacaoModel->findOne(
            'id = :id AND usuario_id = :usuario_id',
            ['id' => $id, 'usuario_id' => $usuarioId]
        );

        if (!$notificacao) {
            error_log("Notificação ID: $id não encontrada ou não pertence ao usuário ID: $usuarioId");

            // Se for uma requisição AJAX
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Notificação não encontrada']);
                return;
            }

            set_flash_message('error', 'Notificação não encontrada.');
            redirect('notificacoes');
            return;
        }

        // Excluir notificação
        $sucesso = $this->notificacaoModel->delete($id);
        error_log("Resultado ao excluir notificação ID: $id: " . ($sucesso ? 'Sucesso' : 'Falha'));

        // Se for uma requisição AJAX
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => $sucesso,
                'message' => $sucesso ? 'Notificação excluída' : 'Erro ao excluir notificação'
            ]);
            return;
        }

        if ($sucesso) {
            set_flash_message('success', 'Notificação excluída com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao excluir notificação.');
        }

        redirect('notificacoes');
    }

    /**
     * Exclui todas as notificações do usuário
     */
    public function excluirTodas()
    {
        $usuarioId = get_user_id();

        // Log para depuração
        error_log("Tentativa de excluir todas as notificações para usuário ID: $usuarioId");

        // Excluir todas as notificações do usuário
        $sql = "UPDATE {$this->notificacaoModel->getTable()} 
                SET removido = 1, data_remocao = NOW() 
                WHERE usuario_id = :usuario_id AND removido = 0";

        $stmt = $this->notificacaoModel->getDb()->prepare($sql);
        $sucesso = $stmt->execute(['usuario_id' => $usuarioId]);

        error_log("Resultado ao excluir todas as notificações: " . ($sucesso ? 'Sucesso' : 'Falha'));

        // Se for uma requisição AJAX
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => $sucesso,
                'message' => $sucesso ? 'Todas as notificações foram excluídas' : 'Erro ao excluir notificações'
            ]);
            return;
        }

        if ($sucesso) {
            set_flash_message('success', 'Todas as notificações foram excluídas.');
        } else {
            set_flash_message('error', 'Erro ao excluir notificações.');
        }

        redirect('notificacoes');
    }

    /**
     * Associa o usuário atual a um setor
     * 
     * @param int $setorId ID do setor
     */
    public function associarAoSetor($setorId)
    {
        $usuarioId = get_user_id();

        // Verificar se o setor existe
        $sql = "SELECT * FROM setores WHERE id = :setor_id";
        $stmt = $this->notificacaoModel->getDb()->prepare($sql);
        $stmt->bindValue(':setor_id', $setorId);
        $stmt->execute();

        $setor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$setor) {
            set_flash_message('error', "Setor ID $setorId não encontrado.");
            redirect('notificacoes/diagnosticarNotificacoes');
            return;
        }

        // Verificar se já existe a associação
        $sql = "SELECT * FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
        $stmt = $this->notificacaoModel->getDb()->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId);
        $stmt->bindValue(':setor_id', $setorId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            set_flash_message('info', "Usuário já está associado ao setor {$setor['nome']} (ID: $setorId).");
            redirect('notificacoes/diagnosticarNotificacoes');
            return;
        }

        // Criar a associação
        try {
            $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_em, criado_por) 
                VALUES (:usuario_id, :setor_id, :principal, :criado_em, :criado_por)";

            $stmt = $this->notificacaoModel->getDb()->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuarioId);
            $stmt->bindValue(':setor_id', $setorId);
            $stmt->bindValue(':principal', 1);
            $stmt->bindValue(':criado_em', date('Y-m-d H:i:s'));
            $stmt->bindValue(':criado_por', $usuarioId);

            $stmt->execute();

            set_flash_message('success', "Usuário associado com sucesso ao setor {$setor['nome']} (ID: $setorId).");
        } catch (Exception $e) {
            set_flash_message('error', "Erro ao associar usuário ao setor: " . $e->getMessage());
        }

        redirect('notificacoes/diagnosticarNotificacoes');
    }

    /**
     * Verifica se a requisição é AJAX
     * 
     * @return bool True se for AJAX, false caso contrário
     */
    private function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Envia uma resposta JSON
     * 
     * @param array $data Dados a serem enviados
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
