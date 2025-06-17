<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/StatusChamado.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/ChamadoComentario.php';
require_once ROOT_DIR . '/app/models/ChamadoHistorico.php';

/**
 * Controlador para gerenciamento de chamados
 */
class ChamadosController extends Controller
{
    private $chamadoModel;
    private $setorModel;
    private $statusModel;
    private $usuarioModel;
    private $comentarioModel;
    private $historicoModel;

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
        $this->statusModel = new StatusChamado();
        $this->usuarioModel = new Usuario();
        $this->comentarioModel = new ChamadoComentario();
        $this->historicoModel = new ChamadoHistorico();
    }

    /**
     * Dashboard de chamados
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // Obter o ano do filtro (padrão: ano atual)
        $anoFiltro = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

        // Estatísticas gerais
        $estatisticas = $this->chamadoModel->getEstatisticas($empresaId);

        // Chamados por status para gráfico
        $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatus($empresaId);

        // Chamados por mês para gráfico (com filtro de ano)
        $chamadosPorMes = $this->chamadoModel->getChamadosPorMes($empresaId, $anoFiltro);

        // Obter anos disponíveis para o filtro
        $anosDisponiveis = $this->chamadoModel->getAnosDisponiveis($empresaId);

        // Chamados recentes
        $chamadosRecentes = $this->chamadoModel->findAll(
            'empresa_id = :empresa_id',
            ['empresa_id' => $empresaId],
            'data_solicitacao DESC',
            10
        );

        // Setores para filtro rápido
        $setores = $this->setorModel->findAll(
            'empresa_id = :empresa_id AND ativo = 1',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Status para filtro rápido
        $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

        // Verificar se temos dados
        if (empty($estatisticas)) {
            $estatisticas = [
                'total' => 0,
                'abertos' => 0,
                'em_andamento' => 0,
                'concluidos' => 0
            ];
        }

        $this->render('chamados/dashboard', [
            'title' => 'Dashboard de Chamados',
            'pageClass' => 'page-dashboard',
            'pageCSS' => ['dashboard.css'],
            'pageJS' => ['dashboard.js'],
            'estatisticas' => $estatisticas,
            'chamadosPorStatus' => $chamadosPorStatus,
            'chamadosPorMes' => $chamadosPorMes,
            'chamadosRecentes' => $chamadosRecentes,
            'setores' => $setores,
            'statusList' => $statusList,
            'anoFiltro' => $anoFiltro,
            'anosDisponiveis' => $anosDisponiveis
        ]);
    }

    /**
     * Imprime um chamado
     */
    public function imprimir($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Obtém o setor do chamado
        $setor = $this->setorModel->findById($chamado['setor_id']);

        // Obtém o status do chamado
        $status = $this->statusModel->findById($chamado['status_id']);

        // Obtém os comentários do chamado
        $comentarios = $this->comentarioModel->getComentariosChamado($id);

        // Obtém o histórico do chamado
        $historico = $this->historicoModel->getHistoricoChamado($id);

        // Obtém a empresa
        require_once ROOT_DIR . '/app/models/Empresa.php';
        $empresaModel = new Empresa();
        $empresa = $empresaModel->findById($empresaId);

        // Renderiza a view de impressão
        $this->render('chamados/imprimir', [
            'chamado' => $chamado,
            'setor' => $setor,
            'status' => $status,
            'comentarios' => $comentarios,
            'historico' => $historico,
            'empresa' => $empresa
        ], false); // false para não incluir o layout padrão
    }

    /**
     * Lista de chamados com filtros avançados
     */
    public function listar()
    {
        $empresaId = get_empresa_id();

        // Obtém os filtros da URL
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $setor = isset($_GET['setor']) && $_GET['setor'] !== '' ? $_GET['setor'] : null;
        $busca = isset($_GET['busca']) && $_GET['busca'] !== '' ? $_GET['busca'] : null;
        $dataInicio = isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '' ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) && $_GET['data_fim'] !== '' ? $_GET['data_fim'] : null;
        $solicitante = isset($_GET['solicitante']) && $_GET['solicitante'] !== '' ? $_GET['solicitante'] : null;
        $tipoServico = isset($_GET['tipo_servico']) && $_GET['tipo_servico'] !== '' ? $_GET['tipo_servico'] : null;
        $ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'recentes';

        try {
            // Log dos filtros recebidos
            error_log('Filtros recebidos: ' . print_r($_GET, true));

            // Constrói a condição de filtro
            $condicao = 'empresa_id = :empresa_id';
            $params = ['empresa_id' => $empresaId];

            if ($status) {
                $condicao .= ' AND status_id = :status_id';
                $params['status_id'] = $status;
            }

            if ($setor) {
                $condicao .= ' AND setor_id = :setor_id';
                $params['setor_id'] = $setor;
            }

            if ($busca) {
                // Simplificando a busca para evitar problemas
                $condicao .= ' AND (descricao LIKE :busca)';
                $params['busca'] = '%' . $busca . '%';
            }

            if ($dataInicio) {
                $condicao .= ' AND data_solicitacao >= :data_inicio';
                $params['data_inicio'] = $dataInicio . ' 00:00:00';
            }

            if ($dataFim) {
                $condicao .= ' AND data_solicitacao <= :data_fim';
                $params['data_fim'] = $dataFim . ' 23:59:59';
            }

            if ($solicitante) {
                $condicao .= ' AND solicitante = :solicitante';
                $params['solicitante'] = $solicitante;
            }

            if ($tipoServico) {
                $condicao .= ' AND tipo_servico = :tipo_servico';
                $params['tipo_servico'] = $tipoServico;
            }

            // Define a ordenação
            $ordenacaoSql = 'data_solicitacao DESC';
            if ($ordenacao === 'antigos') {
                $ordenacaoSql = 'data_solicitacao ASC';
            } elseif ($ordenacao === 'status') {
                $ordenacaoSql = 'status_id ASC, data_solicitacao DESC';
            } elseif ($ordenacao === 'setor') {
                $ordenacaoSql = 'setor_id ASC, data_solicitacao DESC';
            }

            // Log para depuração
            error_log('Condição SQL: ' . $condicao);
            error_log('Parâmetros: ' . print_r($params, true));
            error_log('Ordenação: ' . $ordenacaoSql);

            // Obtém os chamados usando SQL direto para evitar problemas com o método findAll
            $sql = "SELECT * FROM chamados WHERE $condicao ORDER BY $ordenacaoSql";
            $stmt = $this->chamadoModel->getDb()->prepare($sql);

            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }

                $stmt->bindValue(':' . $key, $value, $type);
            }

            $stmt->execute();
            $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtém os setores para o filtro
            $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

            // Obtém os status para o filtro
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            // Obtém os tipos de serviço únicos
            $tiposServico = $this->chamadoModel->getTiposServico($empresaId);

            // Obtém os solicitantes únicos
            $solicitantes = $this->chamadoModel->getSolicitantes($empresaId);

            $this->render('chamados/listar', [
                'chamados' => $chamados,
                'setores' => $setores,
                'statusList' => $statusList,
                'tiposServico' => $tiposServico,
                'solicitantes' => $solicitantes,
                'filtros' => [
                    'status' => $status,
                    'setor' => $setor,
                    'busca' => $busca ?? '',  // Garantir que nunca seja null
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'solicitante' => $solicitante,
                    'tipo_servico' => $tipoServico,
                    'ordenacao' => $ordenacao ?? 'recentes'
                ]
            ]);
        } catch (Exception $e) {
            // Log detalhado do erro
            error_log('Erro ao listar chamados: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());

            // Mensagem para o usuário
            set_flash_message('error', 'Erro ao listar chamados: ' . $e->getMessage());

            // Redireciona para o dashboard
            redirect('chamados');
        }
    }

    /**
     * Visualiza um chamado
     */
    public function visualizar($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Obtém o setor do chamado
        $setor = $this->setorModel->findById($chamado['setor_id']);

        // Obtém o status do chamado
        $status = $this->statusModel->findById($chamado['status_id']);

        // Obtém os comentários do chamado
        $comentarios = $this->comentarioModel->findAll('chamado_id = :chamado_id', ['chamado_id' => $id], 'data_criacao ASC');

        // Obtém o histórico do chamado
        $historico = $this->historicoModel->getHistoricoChamado($id);

        // Obtém os status disponíveis para transição
        $statusDisponiveis = $this->statusModel->findAll(null, null, 'nome ASC');

        // Obtém os setores disponíveis para transferência
        $setoresDisponiveis = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

        $this->render('chamados/visualizar', [
            'chamado' => $chamado,
            'setor' => $setor,
            'status' => $status,
            'comentarios' => $comentarios,
            'historico' => $historico,
            'statusDisponiveis' => $statusDisponiveis,
            'setoresDisponiveis' => $setoresDisponiveis
        ]);
    }

    /**
     * Formulário para criar um novo chamado
     */
    public function criar()
    {
        $empresaId = get_empresa_id();

        // Obtém os setores disponíveis
        $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

        // Obtém os tipos de serviço únicos
        $tiposServico = $this->chamadoModel->getTiposServico($empresaId);

        $this->render('chamados/form', [
            'acao' => 'criar',
            'setores' => $setores,
            'tiposServico' => $tiposServico
        ]);
    }

    /**
     * Salva um novo chamado
     */
    public function store()
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Valida os dados do formulário
        $setor_id = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;
        $solicitante = isset($_POST['solicitante']) ? sanitize_input($_POST['solicitante']) : '';
        $paciente = isset($_POST['paciente']) ? sanitize_input($_POST['paciente']) : null;
        $quarto_leito = isset($_POST['quarto_leito']) ? sanitize_input($_POST['quarto_leito']) : null;
        $descricao = isset($_POST['descricao']) ? sanitize_input($_POST['descricao']) : '';
        $tipo_servico = isset($_POST['tipo_servico']) ? sanitize_input($_POST['tipo_servico']) : null;

        // Validação básica
        if (empty($setor_id) || empty($solicitante) || empty($descricao)) {
            set_flash_message('error', 'Preencha todos os campos obrigatórios.');
            redirect('chamados/criar');
            return;
        }

        // Verifica se o setor existe e pertence à empresa
        $setor = $this->setorModel->findById($setor_id);
        if (!$setor || $setor['empresa_id'] != $empresaId || !$setor['ativo']) {
            set_flash_message('error', 'Setor inválido.');
            redirect('chamados/criar');
            return;
        }

        // Prepara os dados para inserção
        $data = [
            'empresa_id' => $empresaId,
            'setor_id' => $setor_id,
            'status_id' => 1, // Status inicial (Aberto)
            'solicitante' => $solicitante,
            'paciente' => $paciente,
            'quarto_leito' => $quarto_leito,
            'descricao' => $descricao,
            'tipo_servico' => $tipo_servico,
            'data_solicitacao' => date('Y-m-d H:i:s'),
            'data_criacao' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        // Insere o chamado usando SQL direto
        try {
            $sql = "INSERT INTO chamados (
                empresa_id, setor_id, status_id, solicitante, 
                paciente, quarto_leito, descricao, tipo_servico, 
                data_solicitacao, data_criacao, data_atualizacao
            ) VALUES (
                :empresa_id, :setor_id, :status_id, :solicitante, 
                :paciente, :quarto_leito, :descricao, :tipo_servico, 
                :data_solicitacao, :data_criacao, :data_atualizacao
            )";

            $stmt = $this->chamadoModel->getDb()->prepare($sql);
            $stmt->bindValue(':empresa_id', $data['empresa_id'], PDO::PARAM_INT);
            $stmt->bindValue(':setor_id', $data['setor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':status_id', $data['status_id'], PDO::PARAM_INT);
            $stmt->bindValue(':solicitante', $data['solicitante'], PDO::PARAM_STR);
            $stmt->bindValue(':paciente', $data['paciente'], PDO::PARAM_STR);
            $stmt->bindValue(':quarto_leito', $data['quarto_leito'], PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $data['descricao'], PDO::PARAM_STR);
            $stmt->bindValue(':tipo_servico', $data['tipo_servico'], PDO::PARAM_STR);
            $stmt->bindValue(':data_solicitacao', $data['data_solicitacao'], PDO::PARAM_STR);
            $stmt->bindValue(':data_criacao', $data['data_criacao'], PDO::PARAM_STR);
            $stmt->bindValue(':data_atualizacao', $data['data_atualizacao'], PDO::PARAM_STR);

            $stmt->execute();
            $chamadoId = $this->chamadoModel->getDb()->lastInsertId();

            // Registra no histórico usando SQL direto
            $historicoSql = "INSERT INTO historico_chamados (
                chamado_id, setor_id_anterior, setor_id_novo, 
                status_id_anterior, status_id_novo, usuario_id, 
                observacao, data_criacao
            ) VALUES (
                :chamado_id, :setor_id_anterior, :setor_id_novo, 
                :status_id_anterior, :status_id_novo, :usuario_id, 
                :observacao, :data_criacao
            )";

            $historicoStmt = $this->historicoModel->getDb()->prepare($historicoSql);
            $historicoStmt->bindValue(':chamado_id', $chamadoId, PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_anterior', $setor_id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_novo', $setor_id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_anterior', 1, PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_novo', 1, PDO::PARAM_INT);
            $historicoStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $historicoStmt->bindValue(':observacao', 'Chamado criado', PDO::PARAM_STR);
            $historicoStmt->bindValue(':data_criacao', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $historicoStmt->execute();

            set_flash_message('success', 'Chamado criado com sucesso.');
            redirect('chamados/visualizar/' . $chamadoId);
        } catch (Exception $e) {
            error_log('Erro ao criar chamado: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao criar chamado: ' . $e->getMessage());
            redirect('chamados/criar');
        }
    }

    /**
     * Formulário para editar um chamado
     */
    public function editar($id)
    {
        $empresaId = get_empresa_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Obtém os setores disponíveis
        $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

        // Obtém os tipos de serviço únicos
        $tiposServico = $this->chamadoModel->getTiposServico($empresaId);

        $this->render('chamados/form', [
            'acao' => 'editar',
            'chamado' => $chamado,
            'setores' => $setores,
            'tiposServico' => $tiposServico
        ]);
    }

    /**
     * Atualiza um chamado
     */
    public function update($id)
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Valida os dados do formulário
        $setor_id = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;
        $solicitante = isset($_POST['solicitante']) ? sanitize_input($_POST['solicitante']) : '';
        $paciente = isset($_POST['paciente']) ? sanitize_input($_POST['paciente']) : null;
        $quarto_leito = isset($_POST['quarto_leito']) ? sanitize_input($_POST['quarto_leito']) : null;
        $descricao = isset($_POST['descricao']) ? sanitize_input($_POST['descricao']) : '';
        $tipo_servico = isset($_POST['tipo_servico']) ? sanitize_input($_POST['tipo_servico']) : null;

        // Validação básica
        if (empty($setor_id) || empty($solicitante) || empty($descricao)) {
            set_flash_message('error', 'Preencha todos os campos obrigatórios.');
            redirect('chamados/editar/' . $id);
            return;
        }

        // Verifica se o setor existe e pertence à empresa
        $setor = $this->setorModel->findById($setor_id);
        if (!$setor || $setor['empresa_id'] != $empresaId || !$setor['ativo']) {
            set_flash_message('error', 'Setor inválido.');
            redirect('chamados/editar/' . $id);
            return;
        }

        // Prepara os dados para atualização
        $data = [
            'setor_id' => $setor_id,
            'solicitante' => $solicitante,
            'paciente' => $paciente,
            'quarto_leito' => $quarto_leito,
            'descricao' => $descricao,
            'tipo_servico' => $tipo_servico,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        // Atualiza o chamado
        try {
            $this->chamadoModel->update($id, $data);

            // Registra no histórico se o setor foi alterado
            if ($chamado['setor_id'] != $setor_id) {
                $historicoSql = "INSERT INTO historico_chamados (
                    chamado_id, setor_id_anterior, setor_id_novo, 
                    status_id_anterior, status_id_novo, usuario_id, 
                    observacao, data_criacao
                ) VALUES (
                    :chamado_id, :setor_id_anterior, :setor_id_novo, 
                    :status_id_anterior, :status_id_novo, :usuario_id, 
                    :observacao, :data_criacao
                )";

                $historicoStmt = $this->historicoModel->getDb()->prepare($historicoSql);
                $historicoStmt->bindValue(':chamado_id', $id, PDO::PARAM_INT);
                $historicoStmt->bindValue(':setor_id_anterior', $chamado['setor_id'], PDO::PARAM_INT);
                $historicoStmt->bindValue(':setor_id_novo', $setor_id, PDO::PARAM_INT);
                $historicoStmt->bindValue(':status_id_anterior', $chamado['status_id'], PDO::PARAM_INT);
                $historicoStmt->bindValue(':status_id_novo', $chamado['status_id'], PDO::PARAM_INT);
                $historicoStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $historicoStmt->bindValue(':observacao', 'Chamado transferido de setor', PDO::PARAM_STR);
                $historicoStmt->bindValue(':data_criacao', date('Y-m-d H:i:s'), PDO::PARAM_STR);

                $historicoStmt->execute();
            }

            set_flash_message('success', 'Chamado atualizado com sucesso.');
            redirect('chamados/visualizar/' . $id);
        } catch (Exception $e) {
            error_log('Erro ao atualizar chamado: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao atualizar chamado: ' . $e->getMessage());
            redirect('chamados/editar/' . $id);
        }
    }

    /**
     * Altera o status de um chamado
     */
    public function alterarStatus($id)
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Valida os dados do formulário
        $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
        $observacao = isset($_POST['observacao']) ? sanitize_input($_POST['observacao']) : '';

        // Validação básica
        if (empty($status_id)) {
            set_flash_message('error', 'Selecione um status válido.');
            redirect('chamados/visualizar/' . $id);
            return;
        }

        // Verifica se o status existe
        $status = $this->statusModel->findById($status_id);
        if (!$status) {
            set_flash_message('error', 'Status inválido.');
            redirect('chamados/visualizar/' . $id);
            return;
        }

        // Prepara os dados para atualização
        $data = [
            'status_id' => $status_id,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        // Se o status for "Concluído", registra a data de conclusão
        if ($status_id == 4) {
            $data['data_conclusao'] = date('Y-m-d H:i:s');
        } elseif ($chamado['status_id'] == 4 && $status_id != 4) {
            // Se estava concluído e voltou para outro status, remove a data de conclusão
            $data['data_conclusao'] = null;
        }

        // Atualiza o chamado
        try {
            $this->chamadoModel->update($id, $data);

            // Registra no histórico
            $historicoSql = "INSERT INTO historico_chamados (
                chamado_id, setor_id_anterior, setor_id_novo, 
                status_id_anterior, status_id_novo, usuario_id, 
                observacao, data_criacao
            ) VALUES (
                :chamado_id, :setor_id_anterior, :setor_id_novo, 
                :status_id_anterior, :status_id_novo, :usuario_id, 
                :observacao, :data_criacao
            )";

            $historicoStmt = $this->historicoModel->getDb()->prepare($historicoSql);
            $historicoStmt->bindValue(':chamado_id', $id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_anterior', $chamado['setor_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_novo', $chamado['setor_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_anterior', $chamado['status_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_novo', $status_id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $historicoStmt->bindValue(':observacao', $observacao, PDO::PARAM_STR);
            $historicoStmt->bindValue(':data_criacao', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $historicoStmt->execute();

            set_flash_message('success', 'Status do chamado alterado com sucesso.');
            redirect('chamados/visualizar/' . $id);
        } catch (Exception $e) {
            error_log('Erro ao alterar status do chamado: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao alterar status do chamado: ' . $e->getMessage());
            redirect('chamados/visualizar/' . $id);
        }
    }

    /**
     * Transfere um chamado para outro setor
     */
    public function transferirSetor($id)
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Valida os dados do formulário
        $setor_id = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;
        $observacao = isset($_POST['observacao']) ? sanitize_input($_POST['observacao']) : '';

        // Validação básica
        if (empty($setor_id)) {
            set_flash_message('error', 'Selecione um setor válido.');
            redirect('chamados/visualizar/' . $id);
            return;
        }

        // Verifica se o setor existe e pertence à empresa
        $setor = $this->setorModel->findById($setor_id);
        if (!$setor || $setor['empresa_id'] != $empresaId || !$setor['ativo']) {
            set_flash_message('error', 'Setor inválido.');
            redirect('chamados/visualizar/' . $id);
            return;
        }

        // Prepara os dados para atualização
        $data = [
            'setor_id' => $setor_id,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        // Atualiza o chamado
        try {
            $this->chamadoModel->update($id, $data);

            // Registra no histórico usando SQL direto
            $historicoSql = "INSERT INTO historico_chamados (
                chamado_id, setor_id_anterior, setor_id_novo, 
                status_id_anterior, status_id_novo, usuario_id, 
                observacao, data_criacao
            ) VALUES (
                :chamado_id, :setor_id_anterior, :setor_id_novo, 
                :status_id_anterior, :status_id_novo, :usuario_id, 
                :observacao, :data_criacao
            )";

            $historicoStmt = $this->historicoModel->getDb()->prepare($historicoSql);
            $historicoStmt->bindValue(':chamado_id', $id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_anterior', $chamado['setor_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':setor_id_novo', $setor_id, PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_anterior', $chamado['status_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':status_id_novo', $chamado['status_id'], PDO::PARAM_INT);
            $historicoStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $historicoStmt->bindValue(':observacao', $observacao, PDO::PARAM_STR);
            $historicoStmt->bindValue(':data_criacao', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $historicoStmt->execute();

            set_flash_message('success', 'Chamado transferido para outro setor com sucesso.');
            redirect('chamados/visualizar/' . $id);
        } catch (Exception $e) {
            error_log('Erro ao transferir chamado: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao transferir chamado: ' . $e->getMessage());
            redirect('chamados/visualizar/' . $id);
        }
    }

    /**
     * Adiciona um comentário a um chamado
     */
    public function adicionarComentario($id)
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Valida os dados do formulário
        $comentario = isset($_POST['comentario']) ? sanitize_input($_POST['comentario']) : '';

        // Validação básica
        if (empty($comentario)) {
            set_flash_message('error', 'O comentário não pode estar vazio.');
            redirect('chamados/visualizar/' . $id);
            return;
        }

        // Insere o comentário usando SQL direto
        try {
            $comentarioSql = "INSERT INTO chamados_comentarios (
                chamado_id, usuario_id, comentario, data_criacao
            ) VALUES (
                :chamado_id, :usuario_id, :comentario, :data_criacao
            )";

            $comentarioStmt = $this->comentarioModel->getDb()->prepare($comentarioSql);
            $comentarioStmt->bindValue(':chamado_id', $id, PDO::PARAM_INT);
            $comentarioStmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $comentarioStmt->bindValue(':comentario', $comentario, PDO::PARAM_STR);
            $comentarioStmt->bindValue(':data_criacao', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $comentarioStmt->execute();

            set_flash_message('success', 'Comentário adicionado com sucesso.');
            redirect('chamados/visualizar/' . $id);
        } catch (Exception $e) {
            error_log('Erro ao adicionar comentário: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao adicionar comentário: ' . $e->getMessage());
            redirect('chamados/visualizar/' . $id);
        }
    }

    /**
     * Exporta chamados para CSV
     */
    public function exportar()
    {
        $empresaId = get_empresa_id();

        // Obtém os filtros da URL
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $setor = isset($_GET['setor']) ? $_GET['setor'] : null;
        $busca = isset($_GET['busca']) ? $_GET['busca'] : null;
        $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;

        // Constrói a condição de filtro
        $condicao = 'empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        if ($status) {
            $condicao .= ' AND status_id = :status_id';
            $params['status_id'] = $status;
        }

        if ($setor) {
            $condicao .= ' AND setor_id = :setor_id';
            $params['setor_id'] = $setor;
        }

        if ($busca) {
            $condicao .= ' AND (descricao LIKE :busca OR solicitante LIKE :busca)';
            $params['busca'] = '%' . $busca . '%';
        }

        if ($dataInicio) {
            $condicao .= ' AND data_solicitacao >= :data_inicio';
            $params['data_inicio'] = $dataInicio . ' 00:00:00';
        }

        if ($dataFim) {
            $condicao .= ' AND data_solicitacao <= :data_fim';
            $params['data_fim'] = $dataFim . ' 23:59:59';
        }

        // Obtém os chamados
        $chamados = $this->chamadoModel->findAll($condicao, $params, 'data_solicitacao DESC');

        // Prepara o cabeçalho do CSV
        $cabecalho = [
            'ID',
            'Solicitante',
            'Paciente',
            'Quarto/Leito',
            'Descrição',
            'Tipo de Serviço',
            'Setor',
            'Status',
            'Data de Solicitação',
            'Data de Conclusão'
        ];

        // Prepara os dados do CSV
        $dados = [];
        foreach ($chamados as $chamado) {
            // Obtém o nome do setor
            $setor = $this->setorModel->findById($chamado['setor_id']);
            $setorNome = $setor ? $setor['nome'] : 'N/A';

            // Obtém o nome do status
            $status = $this->statusModel->findById($chamado['status_id']);
            $statusNome = $status ? $status['nome'] : 'N/A';

            $dados[] = [
                $chamado['id'],
                $chamado['solicitante'],
                $chamado['paciente'] ?? 'N/A',
                $chamado['quarto_leito'] ?? 'N/A',
                $chamado['descricao'],
                $chamado['tipo_servico'] ?? 'N/A',
                $setorNome,
                $statusNome,
                date('d/m/Y H:i:s', strtotime($chamado['data_solicitacao'])),
                $chamado['data_conclusao'] ? date('d/m/Y H:i:s', strtotime($chamado['data_conclusao'])) : 'N/A'
            ];
        }

        // Define o nome do arquivo
        $nomeArquivo = 'chamados_' . date('Y-m-d_H-i-s') . '.csv';

        // Define os cabeçalhos HTTP para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

        // Cria o arquivo CSV
        $output = fopen('php://output', 'w');

        // Adiciona o BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escreve o cabeçalho
        fputcsv($output, $cabecalho);

        // Escreve os dados
        foreach ($dados as $linha) {
            fputcsv($output, $linha);
        }

        fclose($output);
        exit;
    }

    /**
     * Relatório de chamados
     */
    public function relatorio()
    {
        $empresaId = get_empresa_id();

        // Obtém os filtros da URL
        $anoFiltro = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
        $mesFiltro = isset($_GET['mes']) && $_GET['mes'] !== '' ? (int)$_GET['mes'] : null;
        $setorFiltro = isset($_GET['setor']) && $_GET['setor'] !== '' ? $_GET['setor'] : null;

        // Novos filtros
        $statusFiltro = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $tipoServicoFiltro = isset($_GET['tipo_servico']) && $_GET['tipo_servico'] !== '' ? $_GET['tipo_servico'] : null;
        $solicitanteFiltro = isset($_GET['solicitante']) && $_GET['solicitante'] !== '' ? $_GET['solicitante'] : null;
        $dataInicioFiltro = isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '' ? $_GET['data_inicio'] : null;
        $dataFimFiltro = isset($_GET['data_fim']) && $_GET['data_fim'] !== '' ? $_GET['data_fim'] : null;

        try {
            // Obtém os anos disponíveis para filtro
            $anosDisponiveis = $this->chamadoModel->getAnosDisponiveis($empresaId);

            // Obtém os setores para filtro
            $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

            // Obtém os status para filtro
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            // Obtém os tipos de serviço para filtro
            $tiposServico = $this->chamadoModel->getTiposServico($empresaId);

            // Obtém os solicitantes para filtro
            $solicitantes = $this->chamadoModel->getSolicitantes($empresaId);

            // Aplica os filtros básicos (ano, mês, setor)
            $condicaoBase = "empresa_id = :empresa_id";
            $paramsBase = ['empresa_id' => $empresaId];

            if ($anoFiltro) {
                $condicaoBase .= " AND YEAR(data_solicitacao) = :ano";
                $paramsBase['ano'] = $anoFiltro;
            }

            if ($mesFiltro) {
                $condicaoBase .= " AND MONTH(data_solicitacao) = :mes";
                $paramsBase['mes'] = $mesFiltro;
            }

            if ($setorFiltro) {
                $condicaoBase .= " AND setor_id = :setor_id";
                $paramsBase['setor_id'] = $setorFiltro;
            }

            // Aplica os filtros adicionais
            if ($statusFiltro) {
                $condicaoBase .= " AND status_id = :status_id";
                $paramsBase['status_id'] = $statusFiltro;
            }

            if ($tipoServicoFiltro) {
                $condicaoBase .= " AND tipo_servico = :tipo_servico";
                $paramsBase['tipo_servico'] = $tipoServicoFiltro;
            }

            if ($solicitanteFiltro) {
                $condicaoBase .= " AND solicitante = :solicitante";
                $paramsBase['solicitante'] = $solicitanteFiltro;
            }

            if ($dataInicioFiltro) {
                $condicaoBase .= " AND data_solicitacao >= :data_inicio";
                $paramsBase['data_inicio'] = $dataInicioFiltro . ' 00:00:00';
            }

            if ($dataFimFiltro) {
                $condicaoBase .= " AND data_solicitacao <= :data_fim";
                $paramsBase['data_fim'] = $dataFimFiltro . ' 23:59:59';
            }

            // Obtém os dados para os relatórios
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatusRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $chamadosPorMes = $this->chamadoModel->getChamadosPorMesRelatorio($empresaId, $anoFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $tempoMedioAtendimento = $this->chamadoModel->getTempoMedioAtendimento($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $chamadosPorSetor = $this->chamadoModel->getChamadosPorSetorRelatorio($empresaId, $anoFiltro, $mesFiltro, $condicaoBase, $paramsBase);
            $chamadosPorTipoServico = $this->chamadoModel->getChamadosPorTipoServicoRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $taxaResolucao = $this->chamadoModel->getTaxaResolucaoRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $chamadosPorDiaSemana = $this->chamadoModel->getChamadosPorDiaSemanaRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $evolucaoMensalPorStatus = $this->chamadoModel->getEvolucaoMensalPorStatus($empresaId, $anoFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $estatisticasGerais = $this->chamadoModel->getEstatisticasGerais($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);

            // Renderiza a view
            $this->render('chamados/relatorio', [
                'title' => 'Relatório de Chamados',
                'pageClass' => 'page-relatorio',
                'pageCSS' => ['relatorios.css'],
                'pageJS' => ['relatorios.js'],
                'anosDisponiveis' => $anosDisponiveis,
                'setores' => $setores,
                'statusList' => $statusList,
                'tiposServico' => $tiposServico,
                'solicitantes' => $solicitantes,
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorMes' => $chamadosPorMes,
                'tempoMedioAtendimento' => $tempoMedioAtendimento,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorTipoServico' => $chamadosPorTipoServico,
                'taxaResolucao' => $taxaResolucao,
                'chamadosPorDiaSemana' => $chamadosPorDiaSemana,
                'evolucaoMensalPorStatus' => $evolucaoMensalPorStatus,
                'estatisticasGerais' => $estatisticasGerais,
                'filtros' => [
                    'ano' => $anoFiltro,
                    'mes' => $mesFiltro,
                    'setor' => $setorFiltro,
                    'status' => $statusFiltro,
                    'tipo_servico' => $tipoServicoFiltro,
                    'solicitante' => $solicitanteFiltro,
                    'data_inicio' => $dataInicioFiltro,
                    'data_fim' => $dataFimFiltro
                ]
            ]);
        } catch (Exception $e) {
            // Log do erro
            error_log('Erro ao gerar relatório: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());

            // Mensagem para o usuário
            set_flash_message('error', 'Erro ao gerar relatório: ' . $e->getMessage());

            // Redireciona para o dashboard
            redirect('chamados');
        }
    }
}
