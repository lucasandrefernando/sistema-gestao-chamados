<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/Chamado.php';

/**
 * Controlador para gerenciamento do perfil do usuário
 */
class PerfilController extends Controller
{
    private $usuarioModel;
    private $chamadoModel;

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
        $this->usuarioModel = new Usuario();
        $this->chamadoModel = new Chamado();
    }

    /**
     * Exibe o perfil do usuário
     */
    public function index()
    {
        $usuarioId = get_user_id();

        try {
            // Buscar dados básicos do usuário
            $usuario = $this->usuarioModel->findById($usuarioId);

            if (!$usuario) {
                set_flash_message('error', 'Usuário não encontrado.');
                redirect('dashboard');
                exit;
            }

            // Buscar nome da empresa
            if (isset($usuario['empresa_id']) && $usuario['empresa_id']) {
                try {
                    $sql = "SELECT nome FROM empresas WHERE id = :empresa_id";
                    $stmt = $this->usuarioModel->getDb()->prepare($sql);
                    $stmt->execute(['empresa_id' => $usuario['empresa_id']]);
                    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($empresa) {
                        $usuario['empresa_nome'] = $empresa['nome'];
                    }
                } catch (Exception $e) {
                    error_log('Erro ao buscar empresa: ' . $e->getMessage());
                }
            }

            // Buscar nome do setor
            if (isset($usuario['setor_id']) && $usuario['setor_id']) {
                try {
                    $sql = "SELECT nome FROM setores WHERE id = :setor_id";
                    $stmt = $this->usuarioModel->getDb()->prepare($sql);
                    $stmt->execute(['setor_id' => $usuario['setor_id']]);
                    $setor = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($setor) {
                        $usuario['setor_nome'] = $setor['nome'];
                    }
                } catch (Exception $e) {
                    error_log('Erro ao buscar setor: ' . $e->getMessage());
                }
            }

            // Buscar chamados recentes do usuário
            $chamadosRecentes = [];
            try {
                if (method_exists($this->chamadoModel, 'buscarChamadosRecentes')) {
                    $chamadosRecentes = $this->chamadoModel->buscarChamadosRecentes($usuarioId, 5);
                } else {
                    // Fallback se o método não existir
                    $chamadosRecentes = $this->chamadoModel->findAll(
                        'criado_por = :usuario_id',
                        ['usuario_id' => $usuarioId],
                        'data_criacao DESC',
                        5
                    );
                }
            } catch (Exception $e) {
                error_log('Erro ao buscar chamados recentes: ' . $e->getMessage());
            }

            // Buscar estatísticas de chamados
            $estatisticas = [];
            try {
                if (method_exists($this->usuarioModel, 'getEstatisticasChamadosUsuario')) {
                    $estatisticas = $this->usuarioModel->getEstatisticasChamadosUsuario($usuarioId);
                } else {
                    // Fallback se o método não existir
                    $estatisticas = [
                        'abertos' => 0,
                        'em_andamento' => 0,
                        'concluidos' => 0
                    ];
                }
            } catch (Exception $e) {
                error_log('Erro ao buscar estatísticas: ' . $e->getMessage());
            }

            // Buscar atividades do usuário
            $atividades = [];
            try {
                $atividades = $this->buscarAtividadesUsuario($usuarioId);
            } catch (Exception $e) {
                error_log('Erro ao buscar atividades: ' . $e->getMessage());
            }

            $this->render('perfil/index', [
                'usuario' => $usuario,
                'chamados_recentes' => $chamadosRecentes,
                'estatisticas' => $estatisticas,
                'atividades' => $atividades
            ]);
        } catch (Exception $e) {
            error_log('Erro no método index do PerfilController: ' . $e->getMessage());
            set_flash_message('error', 'Ocorreu um erro ao carregar o perfil. Por favor, tente novamente.');
            redirect('dashboard');
        }
    }

    /**
     * Exibe o formulário para editar o perfil
     */
    public function editar()
    {
        $usuarioId = get_user_id();

        // Buscar dados do usuário
        $usuario = $this->usuarioModel->findById($usuarioId);

        if (!$usuario) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('dashboard');
            exit;
        }

        $this->render('perfil/editar', [
            'usuario' => $usuario
        ]);
    }

    /**
     * Processa a atualização do perfil
     */
    public function atualizar()
    {
        $usuarioId = get_user_id();

        // Buscar dados do usuário
        $usuario = $this->usuarioModel->findById($usuarioId);

        if (!$usuario) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('dashboard');
            exit;
        }

        // Obter dados do formulário
        $nome = $_POST['nome'] ?? '';
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Validar nome
        if (empty($nome)) {
            set_flash_message('error', 'O nome é obrigatório.');
            redirect('perfil/editar');
            exit;
        }

        // Preparar dados para atualização
        $dados = [
            'nome' => $nome
        ];

        // Verificar se deseja alterar a senha
        if (!empty($novaSenha)) {
            // Verificar se a senha atual está correta
            if (!$this->usuarioModel->verificarSenha($senhaAtual, $usuario['senha'])) {
                set_flash_message('error', 'A senha atual está incorreta.');
                redirect('perfil/editar');
                exit;
            }

            // Verificar se as senhas coincidem
            if ($novaSenha !== $confirmarSenha) {
                set_flash_message('error', 'A nova senha e a confirmação não coincidem.');
                redirect('perfil/editar');
                exit;
            }

            // Verificar força da senha
            if (strlen($novaSenha) < 6) {
                set_flash_message('error', 'A nova senha deve ter pelo menos 6 caracteres.');
                redirect('perfil/editar');
                exit;
            }

            // Adicionar senha aos dados
            $dados['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        // Atualizar usuário
        if ($this->usuarioModel->update($usuarioId, $dados)) {
            set_flash_message('success', 'Perfil atualizado com sucesso.');

            // Atualizar nome na sessão
            $_SESSION['user_name'] = $nome;

            redirect('perfil');
        } else {
            set_flash_message('error', 'Erro ao atualizar perfil.');
            redirect('perfil/editar');
        }
    }

    /**
     * Exibe o histórico de atividades do usuário
     */
    public function atividade()
    {
        $usuarioId = get_user_id();

        // Buscar atividades do usuário
        $atividades = $this->buscarAtividadesUsuario($usuarioId);

        $this->render('perfil/atividade', [
            'atividades' => $atividades
        ]);
    }

    /**
     * Busca as atividades do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array Lista de atividades
     */
    private function buscarAtividadesUsuario($usuarioId)
    {
        try {
            // Buscar chamados criados pelo usuário
            $sql = "SELECT 
                    'chamado_criado' as tipo,
                    c.id,
                    c.titulo,
                    c.data_criacao as data,
                    c.status_id,
                    s.nome as status_nome
                FROM chamados c
                LEFT JOIN status_chamado s ON c.status_id = s.id
                WHERE c.criado_por = :usuario_id
                
                UNION
                
                SELECT 
                    'comentario_adicionado' as tipo,
                    cc.chamado_id as id,
                    c.titulo,
                    cc.data_criacao as data,
                    c.status_id,
                    s.nome as status_nome
                FROM chamado_comentarios cc
                JOIN chamados c ON cc.chamado_id = c.id
                LEFT JOIN status_chamado s ON c.status_id = s.id
                WHERE cc.usuario_id = :usuario_id
                
                UNION
                
                SELECT 
                    'chamado_atualizado' as tipo,
                    ch.chamado_id as id,
                    c.titulo,
                    ch.data_alteracao as data,
                    c.status_id,
                    s.nome as status_nome
                FROM chamado_historico ch
                JOIN chamados c ON ch.chamado_id = c.id
                LEFT JOIN status_chamado s ON c.status_id = s.id
                WHERE ch.usuario_id = :usuario_id
                
                ORDER BY data DESC
                LIMIT 50";

            // Usar o método getDb() para obter a conexão com o banco
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao buscar atividades do usuário: ' . $e->getMessage());
            return [];
        }
    }
}
