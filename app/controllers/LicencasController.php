<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Licenca.php';
require_once ROOT_DIR . '/app/models/Empresa.php';

/**
 * Controlador para gerenciamento de licenças
 */
class LicencasController extends Controller
{
    private $licencaModel;
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
            set_flash_message('error', 'Apenas administradores master podem gerenciar licenças. Se você precisa de mais licenças, entre em contato com um administrador master.');
            redirect('dashboard');
            exit;
        }

        // Inicializa os modelos
        $this->licencaModel = new Licenca();
        $this->empresaModel = new Empresa();
    }

    /**
     * Lista de licenças
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // Obtém a lista de licenças
        $licencas = $this->licencaModel->findByEmpresa($empresaId);

        // Obtém informações das empresas
        $empresas = [];
        foreach ($licencas as $licenca) {
            if (!isset($empresas[$licenca['empresa_id']])) {
                $empresa = $this->empresaModel->findById($licenca['empresa_id']);
                $empresas[$licenca['empresa_id']] = $empresa;
            }
        }

        // Adiciona os arquivos CSS e JS específicos
        $this->addCssFile('licencas.css');
        $this->addJsFile('licencas.js');

        $this->render('licencas/index', [
            'licencas' => $licencas,
            'empresas' => $empresas,
            'titulo' => 'Gerenciamento de Licenças'
        ]);
    }

    /**
     * Formulário para criar licença
     */
    public function criar()
    {
        // Obtém a lista de empresas
        $empresas = $this->empresaModel->findAll('ativo = 1', [], 'nome ASC');

        // Adiciona os arquivos CSS e JS específicos
        $this->addCssFile('licencas.css');
        $this->addJsFile('licencas.js');

        $this->render('licencas/form', [
            'titulo' => 'Nova Licença',
            'acao' => 'criar',
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa a criação de licença
     */
    public function store()
    {
        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'empresa_id' => 'Empresa',
            'quantidade' => 'Quantidade',
            'data_inicio' => 'Data de Início',
            'data_fim' => 'Data de Fim'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('licencas/criar');
            return;
        }

        // Verifica se já existe uma licença para esta empresa
        $licencasExistentes = $this->licencaModel->findByEmpresa($data['empresa_id']);
        if (!empty($licencasExistentes)) {
            set_flash_message('error', 'Já existe uma licença para esta empresa. Cada empresa pode ter apenas uma licença.');
            redirect('licencas/criar');
            return;
        }

        // Valida a quantidade
        if ($data['quantidade'] <= 0) {
            set_flash_message('error', 'A quantidade deve ser maior que zero.');
            redirect('licencas/criar');
            return;
        }

        // Valida as datas
        $dataInicio = strtotime($data['data_inicio']);
        $dataFim = strtotime($data['data_fim']);

        if ($dataFim <= $dataInicio) {
            set_flash_message('error', 'A data de fim deve ser posterior à data de início.');
            redirect('licencas/criar');
            return;
        }

        // Define valores padrão
        $data['ativo'] = 1;

        // Cria a licença
        $id = $this->licencaModel->create($data);

        if ($id) {
            set_flash_message('success', 'Licença criada com sucesso.');
            redirect('licencas');
        } else {
            set_flash_message('error', 'Erro ao criar licença.');
            redirect('licencas/criar');
        }
    }


    /**
     * Formulário para editar licença
     */
    public function editar($id)
    {
        // Obtém a licença
        $licenca = $this->licencaModel->findById($id);

        // Verifica se a licença existe
        if (!$licenca) {
            set_flash_message('error', 'Licença não encontrada.');
            redirect('licencas');
            return;
        }

        // Obtém a lista de empresas
        $empresas = $this->empresaModel->findAll('ativo = 1', [], 'nome ASC');

        // Adiciona os arquivos CSS e JS específicos
        $this->addCssFile('licencas.css');
        $this->addJsFile('licencas.js');

        $this->render('licencas/form', [
            'titulo' => 'Editar Licença',
            'acao' => 'editar',
            'licenca' => $licenca,
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa a atualização de licença
     */
    public function update($id)
    {
        // Obtém a licença
        $licenca = $this->licencaModel->findById($id);

        // Verifica se a licença existe
        if (!$licenca) {
            set_flash_message('error', 'Licença não encontrada.');
            redirect('licencas');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Verifica se está alterando a empresa
        if (isset($data['empresa_id']) && $data['empresa_id'] != $licenca['empresa_id']) {
            // Verifica se já existe uma licença para a nova empresa
            $licencasExistentes = $this->licencaModel->findByEmpresa($data['empresa_id']);
            if (!empty($licencasExistentes)) {
                set_flash_message('error', 'Já existe uma licença para esta empresa. Cada empresa pode ter apenas uma licença.');
                redirect('licencas/editar/' . $id);
                return;
            }
        }

        // Valida os campos obrigatórios
        $requiredFields = [
            'empresa_id' => 'Empresa',
            'quantidade' => 'Quantidade',
            'data_inicio' => 'Data de Início',
            'data_fim' => 'Data de Fim'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('licencas/editar/' . $id);
            return;
        }

        // Valida a quantidade
        if ($data['quantidade'] <= 0) {
            set_flash_message('error', 'A quantidade deve ser maior que zero.');
            redirect('licencas/editar/' . $id);
            return;
        }

        // Valida as datas
        $dataInicio = strtotime($data['data_inicio']);
        $dataFim = strtotime($data['data_fim']);

        if ($dataFim <= $dataInicio) {
            set_flash_message('error', 'A data de fim deve ser posterior à data de início.');
            redirect('licencas/editar/' . $id);
            return;
        }

        // Atualiza a licença
        if ($this->licencaModel->update($id, $data)) {
            set_flash_message('success', 'Licença atualizada com sucesso.');
            redirect('licencas');
        } else {
            set_flash_message('error', 'Erro ao atualizar licença.');
            redirect('licencas/editar/' . $id);
        }
    }

    /**
     * Ativa/desativa uma licença
     */
    public function toggle($id)
    {
        // Obtém a licença
        $licenca = $this->licencaModel->findById($id);

        // Verifica se a licença existe
        if (!$licenca) {
            set_flash_message('error', 'Licença não encontrada.');
            redirect('licencas');
            return;
        }

        // Alterna o status
        $novoStatus = $licenca['ativo'] ? 0 : 1;

        // Usar apenas a coluna 'ativo' que sabemos que existe
        $data = [
            'ativo' => $novoStatus
        ];

        if ($this->licencaModel->update($id, $data)) {
            $mensagem = $novoStatus ? 'Licença ativada com sucesso.' : 'Licença desativada com sucesso.';
            set_flash_message('success', $mensagem);
        } else {
            set_flash_message('error', 'Erro ao alterar status da licença.');
        }

        redirect('licencas');
    }

    /**
     * Registra log de atividade
     */
    private function registrarLog($acao, $licencaId)
    {
        if (class_exists('Log')) {
            $logModel = new Log();

            // Obter o ID do usuário atual
            $usuarioId = get_user_id();

            // Data atual formatada
            $dataAtual = date('Y-m-d H:i:s');

            // Chamar o método registrar com os argumentos separados
            $logModel->registrar($usuarioId, $acao, 'licencas', $licencaId);
        }
    }


    /**
     * Adiciona arquivo CSS específico
     */
    private function addCssFile($filename)
    {
        // Implementar conforme sua estrutura de template
        // Exemplo: $this->view->addCss($filename);
    }

    /**
     * Adiciona arquivo JS específico
     */
    private function addJsFile($filename)
    {
        // Implementar conforme sua estrutura de template
        // Exemplo: $this->view->addJs($filename);
    }
}
