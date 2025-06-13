<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';

/**
 * Controlador para o dashboard
 */
class DashboardController extends Controller
{
    private $chamadoModel;
    private $setorModel;

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
        $this->chamadoModel = new Chamado();
        $this->setorModel = new Setor();
    }

    /**
     * Página do dashboard
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // Obtém estatísticas para o dashboard
        $estatisticas = $this->chamadoModel->getEstatisticas($empresaId);

        // Obtém a lista de setores
        $setores = $this->setorModel->findAll('ativo = 1', [], 'nome ASC');

        $this->render('dashboard/index', [
            'estatisticas' => $estatisticas,
            'setores' => $setores
        ]);
    }
}
