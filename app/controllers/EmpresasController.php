<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Empresa.php';

/**
 * Controlador para gerenciamento de empresas
 */
class EmpresasController extends Controller
{
    private $empresaModel;
    private $usuarioAtual;
    private $isAdminMaster;
    private $isAdminRegular;
    private $empresaIdUsuario;

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

        // Obtém informações do usuário atual
        $this->usuarioAtual = $_SESSION['usuario'] ?? null;
        $this->isAdminMaster = is_admin_master();
        $this->isAdminRegular = is_admin_regular();
        $this->empresaIdUsuario = get_empresa_id();

        // Se não for admin master e estiver tentando acessar a lista de empresas, redireciona para a edição da sua empresa
        if (!$this->isAdminMaster && $this->isAdminRegular && strpos($_SERVER['REQUEST_URI'], 'empresas') !== false && strpos($_SERVER['REQUEST_URI'], 'empresas/editar') === false) {
            redirect('empresas/editar/' . $this->empresaIdUsuario);
            exit;
        }

        // Inicializa o modelo
        $this->empresaModel = new Empresa();
    }

    /**
     * Lista de empresas
     */
    public function index()
    {
        // Se for admin regular, redireciona para a página de edição da sua empresa
        if ($this->isAdminRegular) {
            redirect('empresas/editar/' . $this->empresaIdUsuario);
            return;
        }

        // Obtém a lista de empresas
        $empresas = $this->empresaModel->findAll([], [], 'nome ASC');

        $this->render('empresas/index', [
            'empresas' => $empresas,
            'isAdminMaster' => $this->isAdminMaster
        ]);
    }

    /**
     * Formulário para criar empresa
     */
    public function criar()
    {
        // Apenas admin master pode criar novas empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Você não tem permissão para criar novas empresas.');
            redirect('dashboard');
            return;
        }

        // Limpa os dados do formulário armazenados na sessão
        unset($_SESSION['form_data']);

        $this->render('empresas/form', [
            'titulo' => 'Nova Empresa',
            'acao' => 'criar',
            'empresa' => [], // Inicializa vazio para evitar erros no template
            'isAdminMaster' => $this->isAdminMaster
        ]);
    }

    /**
     * Processa a criação de empresa
     */
    public function store()
    {
        // Apenas admin master pode criar novas empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Você não tem permissão para criar novas empresas.');
            redirect('dashboard');
            return;
        }

        // Limpa os dados do formulário armazenados na sessão ao finalizar
        $clearFormData = function () {
            unset($_SESSION['form_data']);
        };

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome',
            'cnpj' => 'CNPJ',
            'email' => 'E-mail'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        // Normaliza o CNPJ (remove caracteres não numéricos)
        $data['cnpj'] = preg_replace('/[^0-9]/', '', $data['cnpj']);

        // Verifica se o CNPJ já existe
        if ($this->empresaModel->cnpjExiste($data['cnpj'])) {
            $errors[] = 'Já existe uma empresa cadastrada com este CNPJ.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));

            // Armazena os dados do formulário na sessão para recuperá-los no formulário
            $_SESSION['form_data'] = $data;

            redirect('empresas/criar');
            return;
        }

        // Define valores padrão
        $data['ativo'] = 1;

        // Cria a empresa
        $id = $this->empresaModel->create($data);

        if ($id) {
            $clearFormData();
            set_flash_message('success', 'Empresa criada com sucesso.');
            redirect('empresas');
        } else {
            set_flash_message('error', 'Erro ao criar empresa.');

            // Armazena os dados do formulário na sessão para recuperá-los no formulário
            $_SESSION['form_data'] = $data;

            redirect('empresas/criar');
        }
    }

    /**
     * Formulário para editar empresa
     */
    public function editar($id)
    {
        // Se for admin regular, só pode visualizar sua própria empresa
        if ($this->isAdminRegular && $id != $this->empresaIdUsuario) {
            set_flash_message('error', 'Você só pode visualizar sua própria empresa.');
            redirect('dashboard');
            return;
        }

        // Limpa os dados do formulário armazenados na sessão
        unset($_SESSION['form_data']);

        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect($this->isAdminMaster ? 'empresas' : 'dashboard');
            return;
        }

        $this->render('empresas/form', [
            'titulo' => 'Editar Empresa',
            'acao' => 'editar',
            'empresa' => $empresa,
            'isAdminMaster' => $this->isAdminMaster,
            'isAdminRegular' => $this->isAdminRegular
        ]);
    }

    /**
     * Processa a atualização de empresa
     */
    public function update($id)
    {
        // Apenas admin master pode atualizar empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Apenas administradores master podem alterar os registros cadastrais.');
            redirect('empresas/editar/' . $id);
            return;
        }

        // Limpa os dados do formulário armazenados na sessão ao finalizar
        $clearFormData = function () {
            unset($_SESSION['form_data']);
        };

        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect($this->isAdminMaster ? 'empresas' : 'dashboard');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome',
            'cnpj' => 'CNPJ',
            'email' => 'E-mail'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        // Normaliza o CNPJ (remove caracteres não numéricos)
        $data['cnpj'] = preg_replace('/[^0-9]/', '', $data['cnpj']);

        // Verifica se o CNPJ já existe (excluindo a própria empresa da verificação)
        if ($this->empresaModel->cnpjExiste($data['cnpj'], $id)) {
            $errors[] = 'Já existe outra empresa cadastrada com este CNPJ.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));

            // Armazena os dados do formulário na sessão para recuperá-los no formulário
            $_SESSION['form_data'] = $data;

            redirect('empresas/editar/' . $id);
            return;
        }

        // Atualiza a empresa
        if ($this->empresaModel->update($id, $data)) {
            $clearFormData();
            set_flash_message('success', 'Empresa atualizada com sucesso.');
            redirect($this->isAdminMaster ? 'empresas' : 'dashboard');
        } else {
            set_flash_message('error', 'Erro ao atualizar empresa.');

            // Armazena os dados do formulário na sessão para recuperá-los no formulário
            $_SESSION['form_data'] = $data;

            redirect('empresas/editar/' . $id);
        }
    }

    /**
     * Ativa/desativa uma empresa
     */
    public function toggle($id)
    {
        // Apenas admin master pode ativar/desativar empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Você não tem permissão para ativar/desativar empresas.');
            redirect('dashboard');
            return;
        }

        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect('empresas');
            return;
        }

        // Alterna o status
        $novoStatus = $empresa['ativo'] ? 0 : 1;

        if ($this->empresaModel->update($id, ['ativo' => $novoStatus])) {
            $mensagem = $novoStatus ? 'Empresa ativada com sucesso.' : 'Empresa desativada com sucesso.';
            set_flash_message('success', $mensagem);
        } else {
            set_flash_message('error', 'Erro ao alterar status da empresa.');
        }

        redirect('empresas');
    }

    /**
     * Exibe tela de confirmação para exclusão de empresa
     */
    public function confirmarExclusao($id)
    {
        // Apenas admin master pode excluir empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Você não tem permissão para excluir empresas.');
            redirect('dashboard');
            return;
        }

        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect('empresas');
            return;
        }

        // Obtém informações sobre dependências
        $dependencias = $this->obterDependencias($id);

        $this->render('empresas/confirmar_exclusao', [
            'empresa' => $empresa,
            'dependencias' => $dependencias,
            'isAdminMaster' => $this->isAdminMaster
        ]);
    }

    /**
     * Obtém as dependências de uma empresa
     */
    private function obterDependencias($empresaId)
    {
        require_once ROOT_DIR . '/app/models/Usuario.php';
        require_once ROOT_DIR . '/app/models/Licenca.php';
        require_once ROOT_DIR . '/app/models/Chamado.php';

        $usuarioModel = new Usuario();
        $licencaModel = new Licenca();
        $chamadoModel = new Chamado();

        // Corrigindo as consultas para usar o formato correto de condição e parâmetros
        return [
            'usuarios' => $usuarioModel->findAll('empresa_id = :empresa_id AND removido = 0', ['empresa_id' => $empresaId]),
            'licencas' => $licencaModel->findAll('empresa_id = :empresa_id', ['empresa_id' => $empresaId]),
            'chamados' => $chamadoModel->findAll('empresa_id = :empresa_id', ['empresa_id' => $empresaId])
        ];
    }

    /**
     * Processa a exclusão de empresa
     */
    public function excluir($id)
    {
        // Apenas admin master pode excluir empresas
        if (!$this->isAdminMaster) {
            set_flash_message('error', 'Você não tem permissão para excluir empresas.');
            redirect('dashboard');
            return;
        }

        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect('empresas');
            return;
        }

        // Verifica se o usuário confirmou a exclusão
        if (!isset($_POST['confirmar']) || $_POST['confirmar'] !== 'SIM') {
            set_flash_message('error', 'Você precisa confirmar a exclusão digitando "SIM".');
            redirect('empresas/confirmarExclusao/' . $id);
            return;
        }

        // Inicia uma transação para garantir integridade
        $this->empresaModel->beginTransaction();

        try {
            // Registra log antes da exclusão
            $this->registrarLogExclusao($empresa);

            // Marca usuários como removidos (soft delete)
            $this->marcarUsuariosComoRemovidos($id);

            // Exclui a empresa (as restrições CASCADE no banco de dados cuidarão das licenças e chamados)
            if ($this->empresaModel->delete($id)) {
                $this->empresaModel->commit();
                set_flash_message('success', 'Empresa "' . $empresa['nome'] . '" e todos os seus dados relacionados foram excluídos com sucesso.');
            } else {
                throw new Exception("Erro ao excluir empresa.");
            }
        } catch (Exception $e) {
            $this->empresaModel->rollback();
            set_flash_message('error', 'Erro ao excluir empresa: ' . $e->getMessage());
        }

        redirect('empresas');
    }

    /**
     * Marca usuários como removidos (soft delete)
     */
    private function marcarUsuariosComoRemovidos($empresaId)
    {
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();

        $usuarios = $usuarioModel->findAll('empresa_id = :empresa_id AND removido = 0', ['empresa_id' => $empresaId]);

        foreach ($usuarios as $usuario) {
            $usuarioModel->update($usuario['id'], [
                'removido' => 1,
                'data_remocao' => date('Y-m-d H:i:s'),
                'ativo' => 0
            ]);
        }
    }

    /**
     * Registra log de exclusão de empresa
     */
    private function registrarLogExclusao($empresa)
    {
        require_once ROOT_DIR . '/app/models/Log.php';
        $logModel = new Log();

        $usuarioId = $_SESSION['usuario']['id'] ?? null;

        $logModel->registrar(
            $usuarioId,
            null,
            'exclusao_empresa',
            'Exclusão da empresa "' . $empresa['nome'] . '" (ID: ' . $empresa['id'] . ')',
            json_encode($empresa),
            null
        );
    }
}
