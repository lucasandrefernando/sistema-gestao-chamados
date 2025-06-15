<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Empresa.php';

/**
 * Controlador para gerenciamento de empresas
 */
class EmpresasController extends Controller
{
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

        // Se não for admin master, redireciona para o dashboard
        if (!is_admin_master()) {
            set_flash_message('error', 'Apenas administradores master podem gerenciar empresas.');
            redirect('dashboard');
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
        // Obtém a lista de empresas
        $empresas = $this->empresaModel->findAll([], [], 'nome ASC');

        $this->render('empresas/index', [
            'empresas' => $empresas
        ]);
    }

    /**
     * Formulário para criar empresa
     */
    public function criar()
    {
        $this->render('empresas/form', [
            'titulo' => 'Nova Empresa',
            'acao' => 'criar',
            'empresa' => [] // Inicializa vazio para evitar erros no template
        ]);
    }

    /**
     * Processa a criação de empresa
     */
    public function store()
    {
        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome',
            'cnpj' => 'CNPJ',
            'email' => 'E-mail'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('empresas/criar');
            return;
        }

        // Define valores padrão
        $data['ativo'] = 1;

        // Cria a empresa
        $id = $this->empresaModel->create($data);

        if ($id) {
            set_flash_message('success', 'Empresa criada com sucesso.');
            redirect('empresas');
        } else {
            set_flash_message('error', 'Erro ao criar empresa.');
            redirect('empresas/criar');
        }
    }

    /**
     * Formulário para editar empresa
     */
    public function editar($id)
    {
        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect('empresas');
            return;
        }

        $this->render('empresas/form', [
            'titulo' => 'Editar Empresa',
            'acao' => 'editar',
            'empresa' => $empresa
        ]);
    }

    /**
     * Processa a atualização de empresa
     */
    public function update($id)
    {
        // Obtém a empresa
        $empresa = $this->empresaModel->findById($id);

        // Verifica se a empresa existe
        if (!$empresa) {
            set_flash_message('error', 'Empresa não encontrada.');
            redirect('empresas');
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

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('empresas/editar/' . $id);
            return;
        }

        // Atualiza a empresa
        if ($this->empresaModel->update($id, $data)) {
            set_flash_message('success', 'Empresa atualizada com sucesso.');
            redirect('empresas');
        } else {
            set_flash_message('error', 'Erro ao atualizar empresa.');
            redirect('empresas/editar/' . $id);
        }
    }

    /**
     * Ativa/desativa uma empresa
     */
    public function toggle($id)
    {
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
            'dependencias' => $dependencias
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

        return [
            'usuarios' => $usuarioModel->findAll(['empresa_id' => $empresaId, 'removido' => 0]),
            'licencas' => $licencaModel->findAll(['empresa_id' => $empresaId]),
            'chamados' => $chamadoModel->findAll(['empresa_id' => $empresaId])
        ];
    }

    /**
     * Processa a exclusão de empresa
     */
    public function excluir($id)
    {
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

        $usuarios = $usuarioModel->findAll(['empresa_id' => $empresaId, 'removido' => 0]);

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
