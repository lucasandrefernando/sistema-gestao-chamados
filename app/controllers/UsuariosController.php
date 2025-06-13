<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/Empresa.php';

/**
 * Controlador para gerenciamento de usuários
 */
class UsuariosController extends Controller
{
    private $usuarioModel;
    private $empresaModel;

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

        // Se não for admin, redireciona para o dashboard
        if (!is_admin()) {
            set_flash_message('error', 'Você não tem permissão para acessar esta página.');
            redirect('dashboard');
            exit;
        }

        // Inicializa os modelos
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
    }

    /**
     * Lista de usuários
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // Obtém a lista de usuários
        $usuarios = $this->usuarioModel->findByEmpresa($empresaId);

        $this->render('usuarios/index', [
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Formulário para criar usuário
     */
    public function criar()
    {
        $empresaId = get_empresa_id();

        // Verifica se há licenças disponíveis
        if (!$this->usuarioModel->verificarLicencasDisponiveis($empresaId)) {
            set_flash_message('error', 'Não há licenças disponíveis para criar novos usuários.');
            redirect('usuarios');
            return;
        }

        // Obtém a lista de empresas (para admin global)
        $empresas = $this->empresaModel->findAll('ativo = 1', [], 'nome ASC');

        $this->render('usuarios/form', [
            'titulo' => 'Novo Usuário',
            'acao' => 'criar',
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa a criação de usuário
     */
    public function store()
    {
        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome',
            'email' => 'E-mail',
            'senha' => 'Senha',
            'empresa_id' => 'Empresa'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('usuarios/criar');
            return;
        }

        // Define valores padrão
        $data['admin'] = isset($data['admin']) ? 1 : 0;
        $data['ativo'] = 1;

        // Cria o usuário
        $result = $this->usuarioModel->criarUsuario($data);

        if ($result['success']) {
            set_flash_message('success', $result['message']);
            redirect('usuarios');
        } else {
            set_flash_message('error', $result['message']);
            redirect('usuarios/criar');
        }
    }

    /**
     * Formulário para editar usuário
     */
    public function editar($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o usuário
        $usuario = $this->usuarioModel->findById($id);

        // Verifica se o usuário existe e pertence à empresa do usuário logado
        if (!$usuario || $usuario['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('usuarios');
            return;
        }

        // Obtém a lista de empresas (para admin global)
        $empresas = $this->empresaModel->findAll('ativo = 1', [], 'nome ASC');

        $this->render('usuarios/form', [
            'titulo' => 'Editar Usuário',
            'acao' => 'editar',
            'usuario' => $usuario,
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa a atualização de usuário
     */
    public function update($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o usuário
        $usuario = $this->usuarioModel->findById($id);

        // Verifica se o usuário existe e pertence à empresa do usuário logado
        if (!$usuario || $usuario['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('usuarios');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome',
            'email' => 'E-mail',
            'empresa_id' => 'Empresa'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('usuarios/editar/' . $id);
            return;
        }

        // Define valores padrão
        $data['admin'] = isset($data['admin']) ? 1 : 0;

        // Se a senha estiver vazia, remove do array para não atualizar
        if (empty($data['senha'])) {
            unset($data['senha']);
        } else {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        // Atualiza o usuário
        if ($this->usuarioModel->update($id, $data)) {
            set_flash_message('success', 'Usuário atualizado com sucesso.');
            redirect('usuarios');
        } else {
            set_flash_message('error', 'Erro ao atualizar usuário.');
            redirect('usuarios/editar/' . $id);
        }
    }

    /**
     * Ativa/desativa um usuário
     */
    public function toggle($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o usuário
        $usuario = $this->usuarioModel->findById($id);

        // Verifica se o usuário existe e pertence à empresa do usuário logado
        if (!$usuario || $usuario['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('usuarios');
            return;
        }

        // Não permite desativar o próprio usuário
        if ($usuario['id'] == get_user_id()) {
            set_flash_message('error', 'Você não pode desativar seu próprio usuário.');
            redirect('usuarios');
            return;
        }

        // Alterna o status
        $novoStatus = $usuario['ativo'] ? 0 : 1;

        if ($this->usuarioModel->update($id, ['ativo' => $novoStatus])) {
            $mensagem = $novoStatus ? 'Usuário ativado com sucesso.' : 'Usuário desativado com sucesso.';
            set_flash_message('success', $mensagem);
        } else {
            set_flash_message('error', 'Erro ao alterar status do usuário.');
        }

        redirect('usuarios');
    }
}
