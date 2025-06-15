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
        try {
            $estatisticas = $this->chamadoModel->getEstatisticas($empresaId);
        } catch (Exception $e) {
            // Se ocorrer um erro, define estatísticas como um array vazio
            $estatisticas = [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0,
                'cancelados' => 0,
                'ultimos_chamados' => []
            ];
        }

        // Obtém a lista de setores com tratamento de erro
        try {
            $setores = $this->setorModel->findAll(
                'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
                ['empresa_id' => $empresaId],
                'nome ASC'
            );
        } catch (Exception $e) {
            // Se ocorrer um erro, define $setores como um array vazio
            $setores = [];
            // Registra o erro para depuração
            error_log('Erro ao buscar setores: ' . $e->getMessage());
        }

        $this->render('dashboard/index', [
            'estatisticas' => $estatisticas,
            'setores' => $setores
        ]);
    }

    /**
     * Obtém dados para gráficos do dashboard via AJAX
     */
    public function getChartData()
    {
        // Verifica se é uma requisição AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso não permitido']);
            exit;
        }

        $empresaId = get_empresa_id();

        try {
            // Dados para o gráfico de chamados por status
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatus($empresaId);

            // Dados para o gráfico de chamados por setor
            $chamadosPorSetor = $this->chamadoModel->getChamadosPorSetor($empresaId);

            // Dados para o gráfico de chamados por mês
            $chamadosPorMes = $this->chamadoModel->getChamadosPorMes($empresaId);

            echo json_encode([
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorMes' => $chamadosPorMes
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao obter dados: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Obtém dados para o widget de atividades recentes via AJAX
     */
    public function getRecentActivities()
    {
        // Verifica se é uma requisição AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso não permitido']);
            exit;
        }

        $empresaId = get_empresa_id();

        try {
            // Obtém as atividades recentes (últimos chamados, comentários, etc.)
            $atividades = $this->chamadoModel->getRecentActivities($empresaId);

            echo json_encode([
                'atividades' => $atividades
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao obter atividades: ' . $e->getMessage()]);
        }
        exit;
    }
}
