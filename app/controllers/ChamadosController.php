<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/StatusChamado.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/ChamadoComentario.php';
require_once ROOT_DIR . '/app/models/ChamadoHistorico.php';
require_once ROOT_DIR . '/app/models/Notificacao.php';

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
     * Lista de chamados com filtros avançados e paginação
     */
    public function listar()
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Obtém os filtros da URL
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $setor = isset($_GET['setor']) && $_GET['setor'] !== '' ? $_GET['setor'] : null;
        $busca = isset($_GET['busca']) && $_GET['busca'] !== '' ? $_GET['busca'] : null;
        $dataInicio = isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '' ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) && $_GET['data_fim'] !== '' ? $_GET['data_fim'] : null;
        $solicitante = isset($_GET['solicitante']) && $_GET['solicitante'] !== '' ? $_GET['solicitante'] : null;
        $tipoServico = isset($_GET['tipo_servico']) && $_GET['tipo_servico'] !== '' ? $_GET['tipo_servico'] : null;
        $ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'recentes';

        // Parâmetros de paginação
        $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;


        // Parâmetros de paginação
        $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $itensPorPaginaPadrao = 5; // Valor padrão
        $itensPorPagina = isset($_GET['itens_por_pagina']) && is_numeric($_GET['itens_por_pagina'])
            ? (int)$_GET['itens_por_pagina']
            : $itensPorPaginaPadrao;

        // Limita as opções para evitar problemas de desempenho
        $opcoesPorPaginaPermitidas = [5, 10, 25, 50];
        if (!in_array($itensPorPagina, $opcoesPorPaginaPermitidas)) {
            $itensPorPagina = $itensPorPaginaPadrao;
        }

        try {
            // Log dos filtros recebidos
            error_log('Filtros recebidos: ' . print_r($_GET, true));

            // Busca diretamente na tabela usuarios_setores
            $sqlSetores = "SELECT setor_id FROM usuarios_setores WHERE usuario_id = :usuario_id";
            $stmtSetores = $this->usuarioModel->getDb()->prepare($sqlSetores);
            $stmtSetores->bindValue(':usuario_id', $usuarioId);
            $stmtSetores->execute();

            $setoresIds = [];
            while ($row = $stmtSetores->fetch(PDO::FETCH_ASSOC)) {
                $setoresIds[] = $row['setor_id'];
            }

            // Log para depuração
            error_log('Setores permitidos para o usuário: ' . implode(', ', $setoresIds));

            if (empty($setoresIds)) {
                // Se não tiver nenhum setor vinculado, não mostra nenhum chamado
                set_flash_message('warning', 'Você não tem permissão para visualizar chamados. Entre em contato com o administrador.');
                redirect('dashboard');
                return;
            }

            // Constrói a condição de filtro
            $condicao = 'empresa_id = :empresa_id';
            $params = ['empresa_id' => $empresaId];

            // Restringe aos setores permitidos para todos os usuários
            $placeholders = [];
            foreach ($setoresIds as $index => $id) {
                $paramName = 'setor_id_' . $index;
                $placeholders[] = ':' . $paramName;
                $params[$paramName] = $id;
            }

            $condicao .= " AND setor_id IN (" . implode(',', $placeholders) . ")";

            // Log para depuração
            error_log('Condição de restrição de setores: ' . $condicao);
            error_log('Parâmetros: ' . print_r($params, true));

            // Aplica os filtros adicionais
            if ($status) {
                $condicao .= ' AND status_id = :status_id';
                $params['status_id'] = $status;
            }

            if ($setor) {
                // Verifica se o usuário tem permissão para o setor selecionado
                if (!in_array($setor, $setoresIds)) {
                    set_flash_message('warning', 'Você não tem permissão para visualizar chamados deste setor.');
                    redirect('chamados/listar');
                    return;
                }

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
            error_log('Condição SQL final: ' . $condicao);
            error_log('Parâmetros finais: ' . print_r($params, true));
            error_log('Ordenação: ' . $ordenacaoSql);

            // Obtém o total de chamados para a paginação
            $sqlCount = "SELECT COUNT(*) as total FROM chamados WHERE $condicao";
            $stmtCount = $this->chamadoModel->getDb()->prepare($sqlCount);

            // Bind dos parâmetros (todos nomeados agora)
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                $stmtCount->bindValue(':' . $key, $value, $type);
            }

            $stmtCount->execute();
            $totalChamados = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Calcula o total de páginas
            $totalPaginas = ceil($totalChamados / $itensPorPagina);

            // Ajusta a página atual se necessário
            if ($paginaAtual < 1) {
                $paginaAtual = 1;
            } elseif ($paginaAtual > $totalPaginas && $totalPaginas > 0) {
                $paginaAtual = $totalPaginas;
            }

            // Calcula o offset para a consulta
            $offset = ($paginaAtual - 1) * $itensPorPagina;

            // Obtém os chamados com paginação
            $sql = "SELECT * FROM chamados WHERE $condicao ORDER BY $ordenacaoSql LIMIT $itensPorPagina OFFSET $offset";
            $stmt = $this->chamadoModel->getDb()->prepare($sql);

            // Bind dos parâmetros (todos nomeados agora)
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

            // Obtém os setores para o filtro (apenas os que o usuário tem permissão)
            $setores = [];
            foreach ($setoresIds as $setorId) {
                $setor = $this->setorModel->findById($setorId);
                if ($setor && $setor['ativo']) {
                    $setores[] = $setor;
                }
            }

            // Obtém os status para o filtro
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            // Obtém os tipos de serviço únicos (filtrados por setores permitidos)
            $tiposServico = $this->chamadoModel->getTiposServicoPorSetores($empresaId, $setoresIds);

            // Obtém os solicitantes únicos (filtrados por setores permitidos)
            $solicitantes = $this->chamadoModel->getSolicitantesPorSetores($empresaId, $setoresIds);

            // Obtém as estatísticas gerais (filtradas por setores permitidos)
            $estatisticas = $this->chamadoModel->getEstatisticasPorSetores($empresaId, $setoresIds);

            // Obtém dados para os gráficos (filtrados por setores permitidos)
            $anoAtual = date('Y');

            // Gráfico de chamados por status (filtrado por setores permitidos)
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatusPorSetores($empresaId, $setoresIds);

            // Gráfico de chamados por setor (filtrado por setores permitidos)
            $chamadosPorSetor = $this->chamadoModel->getChamadosPorSetorRelatorioPorSetores($empresaId, $setoresIds);

            // Gráfico de chamados por mês (filtrado por setores permitidos)
            $chamadosPorMes = $this->chamadoModel->getChamadosPorMesPorSetores($empresaId, $anoAtual, $setoresIds);

            // Tempo médio de atendimento (filtrado por setores permitidos)
            $tempoMedioAtendimento = $this->chamadoModel->getTempoMedioAtendimentoPorSetores($empresaId, $setoresIds);

            // Informações de paginação
            $paginacao = [
                'pagina_atual' => $paginaAtual,
                'total_paginas' => $totalPaginas,
                'itens_por_pagina' => $itensPorPagina,
                'total_itens' => $totalChamados
            ];

            $this->render('chamados/listar', [
                'chamados' => $chamados,
                'setores' => $setores,
                'statusList' => $statusList,
                'tiposServico' => $tiposServico,
                'solicitantes' => $solicitantes,
                'estatisticas' => $estatisticas,
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorMes' => $chamadosPorMes,
                'tempoMedioAtendimento' => $tempoMedioAtendimento,
                'paginacao' => $paginacao,
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

            // Redireciona para a página de listagem
            redirect('chamados/listar');
        }
    }




    /**
     * Visualiza um chamado
     */
    public function visualizar($id)
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

        // Verifica se o usuário tem permissão para visualizar este chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para visualizar este chamado.');
            redirect('chamados/listar');
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

        // ✅ CORREÇÃO: Obtém TODOS os setores ativos da empresa para transferência
        // Não limita apenas aos setores que o usuário tem permissão
        $setoresDisponiveis = $this->setorModel->findAll(
            'empresa_id = :empresa_id AND ativo = 1',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

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
        $numero_solicitante = isset($_POST['numero_solicitante']) ? sanitize_input($_POST['numero_solicitante']) : null; // ADICIONADO

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
            'numero_solicitante' => $numero_solicitante, // ADICIONADO
            'data_solicitacao' => date('Y-m-d H:i:s'),
            'data_criacao' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];

        // Insere o chamado usando SQL direto
        try {
            $sql = "INSERT INTO chamados (
            empresa_id, setor_id, status_id, solicitante, 
            paciente, quarto_leito, descricao, tipo_servico, 
            numero_solicitante, data_solicitacao, data_criacao, data_atualizacao
        ) VALUES (
            :empresa_id, :setor_id, :status_id, :solicitante, 
            :paciente, :quarto_leito, :descricao, :tipo_servico, 
            :numero_solicitante, :data_solicitacao, :data_criacao, :data_atualizacao
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
            $stmt->bindValue(':numero_solicitante', $data['numero_solicitante'], PDO::PARAM_STR); // ADICIONADO
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

            // Criar notificações para todos os usuários do setor
            $notificacaoModel = new Notificacao();

            // Preparar os dados da notificação
            $descricaoResumida = substr($descricao, 0, 100) . (strlen($descricao) > 100 ? '...' : '');
            $tipoServicoTexto = $tipo_servico ? " - $tipo_servico" : "";

            $dadosNotificacao = [
                'tipo' => 'novo_chamado',
                'titulo' => "Novo chamado #$chamadoId para seu setor",
                'descricao' => "Solicitante: $solicitante$tipoServicoTexto - $descricaoResumida",
                'referencia_id' => $chamadoId,
                'referencia_tipo' => 'chamado'
            ];

            // Notificar todos os usuários do setor
            $notificacaoModel->notificarSetor($setor_id, $dadosNotificacao);

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
        $usuarioId = get_user_id();

        // Obtém o chamado
        $chamado = $this->chamadoModel->findById($id);

        // Verifica se o chamado existe e pertence à empresa do usuário logado
        if (!$chamado || $chamado['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Chamado não encontrado.');
            redirect('chamados');
            return;
        }

        // Verifica se o usuário tem permissão para editar este chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para editar este chamado.');
            redirect('chamados/listar');
            return;
        }

        // Obtém os setores disponíveis (apenas os que o usuário tem permissão)
        $setoresPermitidos = $this->verificarPermissaoSetor($usuarioId);
        $setores = is_array($setoresPermitidos) ? $setoresPermitidos : $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

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

        // Verifica se o usuário tem permissão para atualizar este chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para atualizar este chamado.');
            redirect('chamados/listar');
            return;
        }

        // Valida os dados do formulário
        $setor_id = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;
        $solicitante = isset($_POST['solicitante']) ? sanitize_input($_POST['solicitante']) : '';
        $paciente = isset($_POST['paciente']) ? sanitize_input($_POST['paciente']) : null;
        $quarto_leito = isset($_POST['quarto_leito']) ? sanitize_input($_POST['quarto_leito']) : null;
        $descricao = isset($_POST['descricao']) ? sanitize_input($_POST['descricao']) : '';
        $tipo_servico = isset($_POST['tipo_servico']) ? sanitize_input($_POST['tipo_servico']) : null;
        $numero_solicitante = isset($_POST['numero_solicitante']) ? sanitize_input($_POST['numero_solicitante']) : null; // ADICIONADO

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

        // Verifica se o usuário tem permissão para o novo setor
        if (!$this->verificarPermissaoSetor($usuarioId, $setor_id)) {
            set_flash_message('error', 'Você não tem permissão para transferir o chamado para este setor.');
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
            'numero_solicitante' => $numero_solicitante, // ADICIONADO
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

        // Verifica se o usuário tem permissão para alterar o status deste chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para alterar o status deste chamado.');
            redirect('chamados/listar');
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

            // Criar notificações para todos os usuários do setor
            $notificacaoModel = new Notificacao();

            // Obter nome do usuário que alterou o status
            $usuario = $this->usuarioModel->findById($usuarioId);
            $usuarioNome = $usuario ? $usuario['nome'] : 'Um usuário';

            // Obter nome do status anterior
            $statusAnterior = $this->statusModel->findById($chamado['status_id']);
            $statusAnteriorNome = $statusAnterior ? $statusAnterior['nome'] : 'desconhecido';

            // Determinar o tipo de notificação com base no novo status
            $tipoNotificacao = 'chamado_atualizado';
            if ($status_id == 4) { // Assumindo que 4 é o ID do status "Concluído"
                $tipoNotificacao = 'chamado_concluido';
            } elseif ($status_id == 2) { // Assumindo que 2 é o ID do status "Em andamento"
                $tipoNotificacao = 'chamado_em_andamento';
            } elseif ($status_id == 3) { // Assumindo que 3 é o ID do status "Pendente"
                $tipoNotificacao = 'chamado_pendente';
            }

            // Preparar os dados da notificação
            $dadosNotificacao = [
                'tipo' => $tipoNotificacao,
                'titulo' => "Status do chamado #$id alterado",
                'descricao' => "$usuarioNome alterou o status de '$statusAnteriorNome' para '{$status['nome']}'" . ($observacao ? ". Obs: $observacao" : ""),
                'referencia_id' => $id,
                'referencia_tipo' => 'chamado'
            ];

            // Notificar todos os usuários do setor (exceto o autor da alteração)
            $notificacaoModel->notificarSetorExcetoUsuario($chamado['setor_id'], $usuarioId, $dadosNotificacao);

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

        // Verifica se o usuário tem permissão para transferir este chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para transferir este chamado.');
            redirect('chamados/listar');
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

        // ✅ REMOÇÃO: Comentamos a verificação que impedia transferência para setores sem permissão
        // Agora o usuário pode transferir para qualquer setor ativo da empresa
        /*
    if (!$this->verificarPermissaoSetor($usuarioId, $setor_id)) {
        set_flash_message('error', 'Você não tem permissão para transferir o chamado para este setor.');
        redirect('chamados/visualizar/' . $id);
        return;
    }
    */

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

            // Criar notificações para todos os usuários do novo setor
            $notificacaoModel = new Notificacao();

            // Obter nome do setor anterior para a notificação
            $setorAnterior = $this->setorModel->findById($chamado['setor_id']);
            $setorAnteriorNome = $setorAnterior ? $setorAnterior['nome'] : 'outro setor';

            // Obter nome do usuário que transferiu
            $usuario = $this->usuarioModel->findById($usuarioId);
            $usuarioNome = $usuario ? $usuario['nome'] : 'Um usuário';

            // Preparar os dados da notificação
            $dadosNotificacao = [
                'tipo' => 'chamado_transferido',
                'titulo' => "Chamado #$id transferido para seu setor",
                'descricao' => "$usuarioNome transferiu de $setorAnteriorNome. " . ($observacao ? "Obs: $observacao" : ""),
                'referencia_id' => $id,
                'referencia_tipo' => 'chamado'
            ];

            // Notificar todos os usuários do novo setor
            $notificacaoModel->notificarSetor($setor_id, $dadosNotificacao);

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

        // Verifica se o usuário tem permissão para adicionar comentários a este chamado
        if (!$this->verificarPermissaoSetor($usuarioId, $chamado['setor_id'])) {
            set_flash_message('error', 'Você não tem permissão para adicionar comentários a este chamado.');
            redirect('chamados/listar');
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
            $comentarioId = $this->comentarioModel->getDb()->lastInsertId();

            // Criar notificações para todos os usuários do setor
            $notificacaoModel = new Notificacao();

            // Obter nome do usuário que comentou
            $usuario = $this->usuarioModel->findById($usuarioId);
            $usuarioNome = $usuario ? $usuario['nome'] : 'Um usuário';

            // Preparar os dados da notificação
            $comentarioResumido = substr($comentario, 0, 100) . (strlen($comentario) > 100 ? '...' : '');

            $dadosNotificacao = [
                'tipo' => 'comentario_adicionado',
                'titulo' => "Novo comentário no chamado #$id",
                'descricao' => "$usuarioNome comentou: $comentarioResumido",
                'referencia_id' => $id,
                'referencia_tipo' => 'chamado'
            ];

            // Notificar todos os usuários do setor (exceto o autor do comentário)
            $notificacaoModel->notificarSetorExcetoUsuario($chamado['setor_id'], $usuarioId, $dadosNotificacao);

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
        $usuarioId = get_user_id();

        // Obtém os filtros da URL
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $setor = isset($_GET['setor']) ? $_GET['setor'] : null;
        $busca = isset($_GET['busca']) ? $_GET['busca'] : null;
        $dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
        $dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;

        // Obtém os setores aos quais o usuário tem acesso
        $setoresPermitidos = $this->verificarPermissaoSetor($usuarioId);

        // Constrói a condição de filtro
        $condicao = 'empresa_id = :empresa_id';
        $params = ['empresa_id' => $empresaId];

        // Se o usuário não for admin, restringe aos setores permitidos
        if (is_array($setoresPermitidos) && !empty($setoresPermitidos)) {
            $setoresIds = array_column($setoresPermitidos, 'id');

            if (empty($setoresIds)) {
                // Se não tiver nenhum setor vinculado, não exporta nenhum chamado
                set_flash_message('warning', 'Você não tem permissão para exportar chamados. Entre em contato com o administrador.');
                redirect('chamados/listar');
                return;
            }

            // Adiciona a restrição de setores à condição usando parâmetros nomeados
            $placeholders = [];
            foreach ($setoresIds as $index => $id) {
                $paramName = 'setor_id_' . $index;
                $placeholders[] = ':' . $paramName;
                $params[$paramName] = $id;
            }

            $condicao .= " AND setor_id IN (" . implode(',', $placeholders) . ")";
        }

        if ($status) {
            $condicao .= ' AND status_id = :status_id';
            $params['status_id'] = $status;
        }

        if ($setor) {
            // Verifica se o usuário tem permissão para o setor selecionado
            if (!$this->verificarPermissaoSetor($usuarioId, $setor)) {
                set_flash_message('warning', 'Você não tem permissão para exportar chamados deste setor.');
                redirect('chamados/listar');
                return;
            }

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
        $usuarioId = get_user_id();

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
            // Obtém os setores aos quais o usuário tem acesso
            $setoresPermitidos = $this->verificarPermissaoSetor($usuarioId);

            // Se o usuário não for admin e não tiver setores vinculados, não mostra nenhum relatório
            if (!is_array($setoresPermitidos) && empty($setoresPermitidos)) {
                set_flash_message('warning', 'Você não tem permissão para visualizar relatórios. Entre em contato com o administrador.');
                redirect('dashboard');
                return;
            }

            // Obtém os anos disponíveis para filtro
            $anosDisponiveis = $this->chamadoModel->getAnosDisponiveis($empresaId);

            // Obtém os setores para filtro (apenas os que o usuário tem permissão)
            $setores = is_array($setoresPermitidos) ? $setoresPermitidos : $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');

            // Obtém os status para filtro
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            // Obtém os tipos de serviço para filtro
            $tiposServico = $this->chamadoModel->getTiposServico($empresaId);

            // Obtém os solicitantes para filtro
            $solicitantes = $this->chamadoModel->getSolicitantes($empresaId);

            // Aplica os filtros básicos (ano, mês, setor)
            $condicaoBase = "empresa_id = :empresa_id";
            $paramsBase = ['empresa_id' => $empresaId];

            // Se o usuário não for admin, restringe aos setores permitidos
            if (is_array($setoresPermitidos) && !empty($setoresPermitidos)) {
                $setoresIds = array_column($setoresPermitidos, 'id');

                if (empty($setoresIds)) {
                    // Se não tiver nenhum setor vinculado, não mostra nenhum relatório
                    set_flash_message('warning', 'Você não tem permissão para visualizar relatórios. Entre em contato com o administrador.');
                    redirect('dashboard');
                    return;
                }

                // Adiciona a restrição de setores à condição usando parâmetros nomeados
                $placeholders = [];
                foreach ($setoresIds as $index => $id) {
                    $paramName = 'setor_id_' . $index;
                    $placeholders[] = ':' . $paramName;
                    $paramsBase[$paramName] = $id;
                }

                $condicaoBase .= " AND setor_id IN (" . implode(',', $placeholders) . ")";
            }

            if ($anoFiltro) {
                $condicaoBase .= " AND YEAR(data_solicitacao) = :ano";
                $paramsBase['ano'] = $anoFiltro;
            }

            if ($mesFiltro) {
                $condicaoBase .= " AND MONTH(data_solicitacao) = :mes";
                $paramsBase['mes'] = $mesFiltro;
            }

            if ($setorFiltro) {
                // Verifica se o usuário tem permissão para o setor selecionado
                if (!$this->verificarPermissaoSetor($usuarioId, $setorFiltro)) {
                    set_flash_message('warning', 'Você não tem permissão para visualizar relatórios deste setor.');
                    redirect('chamados/relatorio');
                    return;
                }

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

    /**
     * Exibe os chamados do setor do usuário logado
     */
    public function meus()
    {
        $usuarioId = get_user_id();

        // Buscar dados do usuário para obter o setor
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($usuarioId);

        if (!$usuario || empty($usuario['setor_id'])) {
            set_flash_message('warning', 'Você não está associado a nenhum setor ou não foi possível identificar seu setor.');
            redirect('chamados/listar');
            exit;
        }

        $setorId = $usuario['setor_id'];
        $empresaId = get_empresa_id();

        // Buscar o nome do setor
        require_once ROOT_DIR . '/app/models/Setor.php';
        $setorModel = new Setor();
        $setor = $setorModel->findById($setorId);
        $setorNome = $setor ? $setor['nome'] : 'Setor não identificado';

        // Definir condições de busca
        $condicao = 'c.empresa_id = :empresa_id AND c.setor_id = :setor_id';
        $params = [
            'empresa_id' => $empresaId,
            'setor_id' => $setorId
        ];

        // Verificar se há filtros adicionais
        $filtros = $this->getFiltrosFromRequest();

        // Adicionar filtros à condição
        if (!empty($filtros['status_id'])) {
            $condicao .= ' AND c.status_id = :status_id';
            $params['status_id'] = $filtros['status_id'];
        }

        if (!empty($filtros['prioridade'])) {
            $condicao .= ' AND c.prioridade = :prioridade';
            $params['prioridade'] = $filtros['prioridade'];
        }

        if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
            $condicao .= ' AND c.data_criacao BETWEEN :data_inicio AND :data_fim';
            $params['data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
            $params['data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['busca'])) {
            $condicao .= ' AND (c.titulo LIKE :busca OR c.descricao LIKE :busca OR c.id LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        // Buscar chamados com as condições
        $chamados = $this->chamadoModel->buscarChamadosCompletos($condicao, $params, 'c.data_criacao DESC');

        // Buscar status disponíveis para o filtro
        $statusChamado = $this->statusModel->findAll('ativo = 1', [], 'ordem ASC');

        // Preparar dados para a view
        $data = [
            'chamados' => $chamados,
            'statusChamado' => $statusChamado,
            'filtros' => $filtros,
            'titulo_pagina' => 'Chamados do Setor: ' . $setorNome,
            'descricao_pagina' => 'Visualize e gerencie os chamados do seu setor.',
            'is_meus_chamados' => true // Flag para indicar que estamos na página "Meus Chamados"
        ];

        // Renderizar a view
        $this->render('chamados/listar', $data);
    }

    /**
     * Obtém os filtros da requisição
     * 
     * @return array Filtros da requisição
     */
    private function getFiltrosFromRequest()
    {
        $filtros = [];

        // Filtro de status
        if (isset($_GET['status_id']) && $_GET['status_id'] !== '') {
            $filtros['status_id'] = $_GET['status_id'];
        }

        // Filtro de prioridade
        if (isset($_GET['prioridade']) && $_GET['prioridade'] !== '') {
            $filtros['prioridade'] = $_GET['prioridade'];
        }

        // Filtro de data
        if (isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '') {
            $filtros['data_inicio'] = $_GET['data_inicio'];
        }

        if (isset($_GET['data_fim']) && $_GET['data_fim'] !== '') {
            $filtros['data_fim'] = $_GET['data_fim'];
        }

        // Filtro de busca
        if (isset($_GET['busca']) && $_GET['busca'] !== '') {
            $filtros['busca'] = $_GET['busca'];
        }

        return $filtros;
    }
    /**
     * Busca chamados com filtros aplicados
     * 
     * @param Chamado $chamadoModel Modelo de chamados
     * @param int $empresaId ID da empresa
     * @param array $filtros Filtros a serem aplicados
     * @return array Lista de chamados filtrados
     */
    private function buscarChamadosComFiltros($chamadoModel, $empresaId, $filtros)
    {
        // Constrói a condição SQL
        $condicao = "empresa_id = :empresa_id";
        $params = ['empresa_id' => $empresaId];

        // Aplica os filtros
        if (!empty($filtros['status'])) {
            $condicao .= " AND status_id = :status_id";
            $params['status_id'] = $filtros['status'];
        }

        if (!empty($filtros['setor'])) {
            $condicao .= " AND setor_id = :setor_id";
            $params['setor_id'] = $filtros['setor'];
        }

        if (!empty($filtros['tipo_servico'])) {
            $condicao .= " AND tipo_servico = :tipo_servico";
            $params['tipo_servico'] = $filtros['tipo_servico'];
        }

        if (!empty($filtros['solicitante'])) {
            $condicao .= " AND solicitante LIKE :solicitante";
            $params['solicitante'] = '%' . $filtros['solicitante'] . '%';
        }

        if (!empty($filtros['data_inicio'])) {
            $condicao .= " AND data_solicitacao >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $condicao .= " AND data_solicitacao <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['busca'])) {
            $condicao .= " AND (descricao LIKE :busca OR solicitante LIKE :busca OR paciente LIKE :busca)";
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        // Define a ordenação
        $orderBy = 'data_solicitacao DESC'; // Padrão: mais recentes

        if ($filtros['ordenacao'] === 'antigos') {
            $orderBy = 'data_solicitacao ASC';
        } else if ($filtros['ordenacao'] === 'status') {
            $orderBy = 'status_id ASC, data_solicitacao DESC';
        } else if ($filtros['ordenacao'] === 'setor') {
            $orderBy = 'setor_id ASC, data_solicitacao DESC';
        }

        // Busca os chamados
        return $chamadoModel->findAll($condicao, $params, $orderBy);
    }

    /**
     * Verifica se o usuário tem permissão para acessar um setor
     * 
     * @param int $usuarioId ID do usuário
     * @param int $setorId ID do setor (opcional, se não fornecido verifica todos os setores do usuário)
     * @return array|bool Array de setores permitidos ou true/false se um setor específico for fornecido
     */
    private function verificarPermissaoSetor($usuarioId, $setorId = null)
    {
        // Removemos a verificação de admin - todos os usuários seguem a mesma regra

        // Busca os setores vinculados ao usuário na tabela usuarios_setores
        $sql = "SELECT s.* FROM setores s 
            INNER JOIN usuarios_setores us ON s.id = us.setor_id 
            WHERE us.usuario_id = :usuario_id AND s.ativo = 1";

        $params = ['usuario_id' => $usuarioId];

        // Log para depuração
        error_log('SQL para buscar setores do usuário: ' . $sql);
        error_log('Parâmetros: ' . print_r($params, true));

        // Se um setor específico for fornecido, verifica apenas esse setor
        if ($setorId) {
            $sql .= " AND s.id = :setor_id";
            $params['setor_id'] = $setorId;

            $stmt = $this->setorModel->getDb()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();

            $result = $stmt->rowCount() > 0;

            // Log para depuração
            error_log('Verificando permissão para setor específico ID: ' . $setorId);
            error_log('Usuário tem permissão? ' . ($result ? 'Sim' : 'Não'));

            // Retorna true se o usuário tem acesso ao setor, false caso contrário
            return $result;
        }

        // Busca todos os setores do usuário
        $stmt = $this->setorModel->getDb()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log para depuração
        error_log('Setores encontrados para o usuário: ' . count($setores));
        error_log('Setores: ' . print_r($setores, true));

        return $setores;
    }


    /**
     * Adiciona permissão de setor para o usuário atual
     */
    public function adicionarPermissaoSetor()
    {
        $usuarioId = get_user_id();
        $setorId = 4; // ID do setor de Nutrição

        try {
            // Verifica se já existe a permissão
            $sql = "SELECT * FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':setor_id', $setorId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo "Permissão já existe para o usuário ID $usuarioId no setor ID $setorId.";
            } else {
                // Adiciona a permissão
                $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_por, criado_em) 
                    VALUES (:usuario_id, :setor_id, 1, :criado_por, NOW())";
                $stmt = $this->usuarioModel->getDb()->prepare($sql);
                $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmt->bindValue(':setor_id', $setorId, PDO::PARAM_INT);
                $stmt->bindValue(':criado_por', $usuarioId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo "Permissão adicionada com sucesso para o usuário ID $usuarioId no setor ID $setorId.";
                } else {
                    echo "Erro ao adicionar permissão.";
                }
            }
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }

        exit;
    }

    /**
     * Verifica as permissões de setor do usuário atual
     */
    public function verificarPermissoesSetor()
    {
        $usuarioId = get_user_id();

        try {
            // Obtém informações do usuário
            $usuario = $this->usuarioModel->findById($usuarioId);

            echo "<h1>Informações do Usuário</h1>";
            echo "<p>ID: " . $usuario['id'] . "</p>";
            echo "<p>Nome: " . $usuario['nome'] . "</p>";
            echo "<p>Email: " . $usuario['email'] . "</p>";
            echo "<p>Admin: " . ($usuario['admin'] ? 'Sim' : 'Não') . "</p>";

            // Obtém os setores vinculados ao usuário
            $sql = "SELECT us.*, s.nome as setor_nome 
                FROM usuarios_setores us 
                INNER JOIN setores s ON us.setor_id = s.id 
                WHERE us.usuario_id = :usuario_id";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h1>Permissões de Setor</h1>";
            if (empty($permissoes)) {
                echo "<p>Nenhuma permissão de setor encontrada.</p>";
            } else {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Setor ID</th><th>Setor Nome</th><th>Principal</th></tr>";
                foreach ($permissoes as $permissao) {
                    echo "<tr>";
                    echo "<td>" . $permissao['id'] . "</td>";
                    echo "<td>" . $permissao['setor_id'] . "</td>";
                    echo "<td>" . $permissao['setor_nome'] . "</td>";
                    echo "<td>" . ($permissao['principal'] ? 'Sim' : 'Não') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            // Obtém todos os setores
            $sql = "SELECT * FROM setores WHERE empresa_id = :empresa_id AND ativo = 1 ORDER BY nome ASC";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->bindValue(':empresa_id', $usuario['empresa_id'], PDO::PARAM_INT);
            $stmt->execute();

            $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h1>Todos os Setores</h1>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Tem Permissão?</th><th>Ação</th></tr>";
            foreach ($setores as $setor) {
                $temPermissao = false;
                foreach ($permissoes as $permissao) {
                    if ($permissao['setor_id'] == $setor['id']) {
                        $temPermissao = true;
                        break;
                    }
                }
                echo "<tr>";
                echo "<td>" . $setor['id'] . "</td>";
                echo "<td>" . $setor['nome'] . "</td>";
                echo "<td>" . ($temPermissao ? 'Sim' : 'Não') . "</td>";
                echo "<td>";
                if (!$temPermissao) {
                    echo "<a href='adicionar-permissao-setor?setor_id=" . $setor['id'] . "'>Adicionar Permissão</a>";
                } else {
                    echo "<a href='remover-permissao-setor?setor_id=" . $setor['id'] . "'>Remover Permissão</a>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }

        exit;
    }
}
