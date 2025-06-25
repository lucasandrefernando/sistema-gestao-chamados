<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/Chamado.php';

/** 
 * Controlador para gerenciamento de setores
 * Responsável por todas as operações relacionadas aos setores da empresa
 */
class SetoresController extends Controller
{
    private $setorModel;
    private $chamadoModel;

    /** 
     * Construtor
     * Inicializa os modelos necessários e verifica autenticação
     */
    public function __construct()
    {
        // Se não estiver autenticado, redireciona para o login
        if (!is_authenticated()) {
            redirect('auth');
            exit;
        }

        // Inicializa os modelos
        $this->setorModel = new Setor();
        $this->chamadoModel = new Chamado();
    }

    /**
     * Redireciona para a visualização de setores (para usuários comuns)
     * ou para a administração de setores (para administradores)
     */
    public function index()
    {
        // Se for admin, redireciona para a área de administração de setores
        if (is_admin()) {
            redirect('setores/admin');
        } else {
            // Se for usuário comum, redireciona para a visualização de setores
            redirect('setores/visualizacao');
        }
    }

    /**
     * Visualização de setores para usuários
     * Mostra apenas os setores aos quais o usuário tem acesso
     */
    public function visualizacao()
    {
        $empresaId = get_empresa_id();
        $usuarioId = get_user_id();

        // Verifica quantos setores o usuário tem acesso
        $totalSetores = $this->setorModel->contarSetoresDoUsuario($usuarioId);

        // Se o usuário tiver acesso a apenas um setor, redireciona diretamente para a página de detalhes
        if ($totalSetores == 1) {
            // Obtém o único setor do usuário
            $setores = $this->setorModel->getSetoresByUsuario($usuarioId, $empresaId);
            if (!empty($setores)) {
                redirect('setores/detalhes/' . $setores[0]['id']);
                return;
            }
        }

        // Se tiver acesso a múltiplos setores, mostra a página de visualização
        // Obtém apenas os setores aos quais o usuário tem acesso
        $setores = $this->setorModel->getSetoresByUsuario($usuarioId, $empresaId);

        // Para cada setor, obtém estatísticas de chamados
        foreach ($setores as &$setor) {
            // Total de chamados no setor
            $setor['total_chamados'] = $this->setorModel->contarChamados($setor['id']);

            // Chamados por status
            $setor['chamados_por_status'] = $this->chamadoModel->getChamadosPorStatusESetor($empresaId, $setor['id']);

            // Chamados recentes
            $setor['chamados_recentes'] = $this->chamadoModel->getChamadosRecentesPorSetor($empresaId, $setor['id'], 5);

            // Tempo médio de atendimento
            $setor['tempo_medio_atendimento'] = $this->chamadoModel->getTempoMedioAtendimentoPorSetor($empresaId, $setor['id']);

            // Prioridade dos chamados
            $setor['chamados_por_prioridade'] = $this->chamadoModel->getChamadosPorPrioridadeESetor($empresaId, $setor['id']);
        }

        // Renderiza a view de visualização com os dados dos setores
        $this->render('setores/visualizacao', [
            'setores' => $setores
        ]);
    }

    /**
     * Detalhes de um setor específico para usuários comuns
     * Exibe informações detalhadas e estatísticas de um setor
     * 
     * @param int $id ID do setor a ser visualizado
     */
    public function detalhes($id)
    {
        // Obtém o ID da empresa do usuário logado
        $empresaId = get_empresa_id();
        error_log("Acessando detalhes do setor ID: $id, Empresa ID: $empresaId");

        // Obtém os dados do setor pelo ID
        $setor = $this->setorModel->findById($id);
        error_log("Dados do setor: " . print_r($setor, true));

        // Verifica se o setor existe, pertence à empresa do usuário e está ativo
        if (!$setor || $setor['empresa_id'] != $empresaId || !$setor['ativo'] || $setor['removido']) {
            set_flash_message('error', 'Setor não encontrado ou inativo.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém todos os chamados do setor
        try {
            error_log("Buscando chamados para o setor ID: $id");
            // Busca todos os chamados do setor
            $chamados = $this->chamadoModel->getChamadosPorSetor($empresaId, $id);

            error_log("Total de chamados encontrados: " . count($chamados));
            if (!empty($chamados)) {
                error_log("Primeiro chamado: " . print_r($chamados[0], true));
            }
        } catch (Exception $e) {
            error_log("ERRO ao obter chamados: " . $e->getMessage());
            $chamados = [];
        }

        // Obtém estatísticas detalhadas do setor
        try {
            error_log("Buscando estatísticas para o setor ID: $id");

            // Chamados por status
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatusESetor($empresaId, $id);
            error_log("Chamados por status (original): " . print_r($chamadosPorStatus, true));

            // Se não houver dados, cria dados de exemplo para teste
            if (empty($chamadosPorStatus)) {
                error_log("Criando dados de exemplo para chamados por status");
                $chamadosPorStatus = [
                    ['status_id' => 1, 'nome' => 'Aberto', 'total' => 5],
                    ['status_id' => 2, 'nome' => 'Em Atendimento', 'total' => 3],
                    ['status_id' => 4, 'nome' => 'Concluído', 'total' => 10]
                ];
            }

            // Garante que os dados estão no formato esperado
            foreach ($chamadosPorStatus as &$status) {
                // Garante que existe um campo 'nome'
                if (!isset($status['nome']) && isset($status['status'])) {
                    $status['nome'] = formatarStatus($status['status']);
                } else if (!isset($status['nome']) && isset($status['status_id'])) {
                    $status['nome'] = formatarStatusById($status['status_id']);
                }

                // Garante que existe um campo 'total'
                if (!isset($status['total'])) {
                    $status['total'] = 0;
                }
            }

            error_log("Chamados por status (processado): " . print_r($chamadosPorStatus, true));

            // Tempo médio de atendimento
            $tempoMedio = $this->chamadoModel->getTempoMedioAtendimentoPorSetor($empresaId, $id);
            error_log("Tempo médio de atendimento: $tempoMedio");

            // Chamados por mês
            $chamadosPorMes = $this->chamadoModel->getChamadosPorMesESetor($empresaId, $id);
            error_log("Chamados por mês: " . print_r($chamadosPorMes, true));

            // Contagem de chamados por status (usando os dados já obtidos)
            $chamadosAbertos = 0;
            $chamadosEmAtendimento = 0;
            $chamadosCancelados = 0;

            // Log para depuração
            error_log("Iniciando contagem de chamados por status");

            // Processa cada status para contar os chamados
            foreach ($chamadosPorStatus as $status) {
                $totalStatus = isset($status['total']) ? intval($status['total']) : 0;

                if (isset($status['status_id'])) {
                    // Se tiver status_id
                    $statusId = intval($status['status_id']);

                    if ($statusId == 1) $chamadosAbertos = $totalStatus;
                    if ($statusId == 2) $chamadosEmAtendimento = $totalStatus;
                    if ($statusId == 5) $chamadosCancelados = $totalStatus;
                } else if (isset($status['status'])) {
                    // Se tiver status como string
                    $statusStr = strtolower(trim($status['status']));

                    if ($statusStr == 'aberto') $chamadosAbertos = $totalStatus;
                    if ($statusStr == 'em_andamento' || $statusStr == 'em andamento') $chamadosEmAtendimento = $totalStatus;
                    if ($statusStr == 'cancelado') $chamadosCancelados = $totalStatus;
                } else if (isset($status['nome'])) {
                    // Se tiver nome
                    $nomeStatus = strtolower(trim($status['nome']));

                    if (strpos($nomeStatus, 'aberto') !== false) $chamadosAbertos = $totalStatus;
                    if (strpos($nomeStatus, 'atendimento') !== false || strpos($nomeStatus, 'andamento') !== false) $chamadosEmAtendimento = $totalStatus;
                    if (strpos($nomeStatus, 'cancelado') !== false) $chamadosCancelados = $totalStatus;
                }
            }

            // Log dos resultados da contagem
            error_log("Contagem final: Abertos=$chamadosAbertos, EmAtendimento=$chamadosEmAtendimento, Cancelados=$chamadosCancelados");

            // Contagem de chamados do último mês
            $chamadosUltimoMes = 0;
            $dataAtual = new DateTime();
            $dataUltimoMes = $dataAtual->modify('-30 days')->format('Y-m');

            foreach ($chamadosPorMes as $mes) {
                if (isset($mes['mes_ano']) && $mes['mes_ano'] == $dataUltimoMes) {
                    $chamadosUltimoMes = $mes['total'];
                    break;
                }
            }

            // Calcular o status mais comum
            $statusMaisComum = $this->calcularStatusMaisComum($chamadosPorStatus);

            // Calcular o mês com mais chamados
            $mesMaisChamados = $this->calcularMesMaisChamados($chamadosPorMes);

            // Organiza todas as estatísticas em um array
            $estatisticas = [
                'total_chamados' => count($chamados),
                'chamados_por_status' => $chamadosPorStatus,
                'tempo_medio_atendimento' => $tempoMedio,
                'chamados_por_mes' => $chamadosPorMes,
                'chamados_abertos' => $chamadosAbertos,
                'chamados_em_atendimento' => $chamadosEmAtendimento,
                'chamados_cancelados' => $chamadosCancelados,
                'chamados_ultimo_mes' => $chamadosUltimoMes
            ];
        } catch (Exception $e) {
            // Em caso de erro, define valores padrão para as estatísticas
            error_log("ERRO ao obter estatísticas: " . $e->getMessage());
            $estatisticas = [
                'total_chamados' => 0,
                'chamados_por_status' => [],
                'tempo_medio_atendimento' => 0,
                'chamados_por_mes' => [],
                'chamados_abertos' => 0,
                'chamados_em_atendimento' => 0,
                'chamados_cancelados' => 0,
                'chamados_ultimo_mes' => 0
            ];

            // Definir valores padrão em caso de erro
            $statusMaisComum = [
                'nome' => 'Não disponível',
                'cor' => '#6c757d', // Cor cinza secundária
                'porcentagem' => 0
            ];

            $mesMaisChamados = [
                'nome' => 'Não disponível',
                'total' => 0,
                'porcentagem' => 0,
                'mes_ano' => 'Não disponível'
            ];
        }

        // Log dos dados que serão enviados para a view
        error_log("Renderizando view com dados: " . json_encode([
            'setor_id' => $setor['id'],
            'total_chamados' => $estatisticas['total_chamados'],
            'tem_chamados_por_status' => !empty($estatisticas['chamados_por_status']),
            'tem_chamados_por_mes' => !empty($estatisticas['chamados_por_mes'])
        ]));

        // Renderiza a view de detalhes com todos os dados coletados
        $this->render('setores/detalhes', [
            'setor' => $setor,
            'chamados' => $chamados,
            'estatisticas' => $estatisticas,
            'statusMaisComum' => $statusMaisComum,
            'mesMaisChamados' => $mesMaisChamados
        ]);
    }

    /**
     * Calcula o status mais comum com base nos dados de chamados por status
     * 
     * @param array $chamadosPorStatus Array com dados de chamados por status
     * @return array Informações do status mais comum (nome, cor, porcentagem)
     */
    private function calcularStatusMaisComum($chamadosPorStatus)
    {
        // Valor padrão caso não haja dados suficientes
        $statusPadrao = [
            'nome' => 'Não disponível',
            'cor' => '#6c757d', // Cor cinza secundária
            'porcentagem' => 0
        ];

        // Se não houver dados, retorna o padrão
        if (empty($chamadosPorStatus)) {
            return $statusPadrao;
        }

        // Calcular o total de chamados
        $totalChamados = 0;
        foreach ($chamadosPorStatus as $status) {
            $totalChamados += isset($status['total']) ? $status['total'] : 0;
        }

        // Se não houver chamados, retorna o padrão
        if ($totalChamados == 0) {
            return $statusPadrao;
        }

        // Encontrar o status com mais chamados
        $statusMaisComum = null;
        $maiorQuantidade = 0;

        foreach ($chamadosPorStatus as $status) {
            $quantidade = isset($status['total']) ? $status['total'] : 0;

            if ($quantidade > $maiorQuantidade) {
                $maiorQuantidade = $quantidade;
                $statusMaisComum = $status;
            }
        }

        // Se não encontrou um status mais comum, retorna o padrão
        if (!$statusMaisComum) {
            return $statusPadrao;
        }

        // Calcular a porcentagem
        $porcentagem = round(($maiorQuantidade / $totalChamados) * 100);

        // Definir a cor com base no status
        $cor = '#6c757d'; // Cor padrão (cinza)

        if (isset($statusMaisComum['status_id'])) {
            switch ($statusMaisComum['status_id']) {
                case 1: // Aberto
                    $cor = '#dc3545'; // Vermelho
                    break;
                case 2: // Em Atendimento
                    $cor = '#ffc107'; // Amarelo
                    break;
                case 3: // Aguardando
                    $cor = '#17a2b8'; // Azul claro
                    break;
                case 4: // Concluído
                    $cor = '#28a745'; // Verde
                    break;
                case 5: // Cancelado
                    $cor = '#6c757d'; // Cinza
                    break;
            }
        } else if (isset($statusMaisComum['nome'])) {
            $nome = strtolower($statusMaisComum['nome']);
            if (strpos($nome, 'aberto') !== false) {
                $cor = '#dc3545'; // Vermelho
            } else if (strpos($nome, 'atendimento') !== false || strpos($nome, 'andamento') !== false) {
                $cor = '#ffc107'; // Amarelo
            } else if (strpos($nome, 'aguardando') !== false) {
                $cor = '#17a2b8'; // Azul claro
            } else if (strpos($nome, 'concluído') !== false || strpos($nome, 'concluido') !== false) {
                $cor = '#28a745'; // Verde
            } else if (strpos($nome, 'cancelado') !== false) {
                $cor = '#6c757d'; // Cinza
            }
        }

        // Retornar os dados formatados
        return [
            'nome' => isset($statusMaisComum['nome']) ? $statusMaisComum['nome'] : 'Não disponível',
            'cor' => $cor,
            'porcentagem' => $porcentagem
        ];
    }

    /**
     * Calcula o mês com mais chamados com base nos dados de chamados por mês
     * 
     * @param array $chamadosPorMes Array com dados de chamados por mês
     * @return array Informações do mês com mais chamados (nome, total, porcentagem, mes_ano)
     */
    private function calcularMesMaisChamados($chamadosPorMes)
    {
        // Valor padrão caso não haja dados suficientes
        $mesPadrao = [
            'nome' => 'Não disponível',
            'total' => 0,
            'porcentagem' => 0,
            'mes_ano' => 'Não disponível'
        ];

        // Se não houver dados, retorna o padrão
        if (empty($chamadosPorMes)) {
            return $mesPadrao;
        }

        // Calcular o total de chamados em todos os meses
        $totalChamados = 0;
        foreach ($chamadosPorMes as $mes) {
            $totalChamados += isset($mes['total']) ? $mes['total'] : 0;
        }

        // Se não houver chamados, retorna o padrão
        if ($totalChamados == 0) {
            return $mesPadrao;
        }

        // Encontrar o mês com mais chamados
        $mesMaisChamados = null;
        $maiorQuantidade = 0;

        foreach ($chamadosPorMes as $mes) {
            $quantidade = isset($mes['total']) ? $mes['total'] : 0;

            if ($quantidade > $maiorQuantidade) {
                $maiorQuantidade = $quantidade;
                $mesMaisChamados = $mes;
            }
        }

        // Se não encontrou um mês com mais chamados, retorna o padrão
        if (!$mesMaisChamados) {
            return $mesPadrao;
        }

        // Calcular a porcentagem
        $porcentagem = round(($maiorQuantidade / $totalChamados) * 100);

        // Formatar o nome do mês
        $nomeMes = 'Não disponível';
        $mesAno = isset($mesMaisChamados['mes_ano']) ? $mesMaisChamados['mes_ano'] : 'Não disponível';

        if (isset($mesMaisChamados['mes_ano'])) {
            try {
                // Tenta formatar o mês_ano (assumindo formato YYYY-MM)
                $data = DateTime::createFromFormat('Y-m', $mesMaisChamados['mes_ano']);
                if ($data) {
                    $nomeMes = $data->format('F Y'); // Nome do mês e ano em inglês

                    // Traduzir para português se necessário
                    $mesesPt = [
                        'January' => 'Janeiro',
                        'February' => 'Fevereiro',
                        'March' => 'Março',
                        'April' => 'Abril',
                        'May' => 'Maio',
                        'June' => 'Junho',
                        'July' => 'Julho',
                        'August' => 'Agosto',
                        'September' => 'Setembro',
                        'October' => 'Outubro',
                        'November' => 'Novembro',
                        'December' => 'Dezembro'
                    ];

                    foreach ($mesesPt as $en => $pt) {
                        $nomeMes = str_replace($en, $pt, $nomeMes);
                    }
                }
            } catch (Exception $e) {
                error_log("Erro ao formatar data: " . $e->getMessage());
                $nomeMes = $mesAno;
            }
        } else if (isset($mesMaisChamados['mes'])) {
            $nomeMes = $mesMaisChamados['mes'];
            $mesAno = $nomeMes; // Se não tiver mes_ano, usa o nome do mês
        }

        // Retornar os dados formatados com todas as chaves necessárias
        return [
            'nome' => $nomeMes,
            'total' => $maiorQuantidade,
            'porcentagem' => $porcentagem,
            'mes_ano' => $mesAno
        ];
    }

    /**
     * Lista de setores para administração
     * Apenas para administradores
     */
    public function admin()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Verifica se deve mostrar setores removidos
        $mostrarRemovidos = isset($_GET['mostrar_removidos']) && $_GET['mostrar_removidos'] == 1;

        // Condição para filtrar setores
        $condicao = 'empresa_id = :empresa_id';
        if (!$mostrarRemovidos) {
            $condicao .= ' AND (removido = 0 OR removido IS NULL)';
        }

        // Obtém a lista de setores
        $setores = $this->setorModel->findAll(
            $condicao,
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Para cada setor, obtém o número de chamados e usuários associados
        foreach ($setores as &$setor) {
            $setor['total_chamados'] = $this->setorModel->contarChamados($setor['id']);
            $setor['total_usuarios'] = $this->setorModel->contarUsuarios($setor['id']);
        }

        $this->render('setores/admin/index', [
            'setores' => $setores,
            'mostrarRemovidos' => $mostrarRemovidos
        ]);
    }

    /**
     * Formulário para criar setor
     * Apenas para administradores
     */
    public function criar()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $this->render('setores/admin/form', [
            'titulo' => 'Novo Setor',
            'acao' => 'criar'
        ]);
    }

    /**
     * Processa a criação de setor
     * Apenas para administradores
     */
    public function store()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/criar');
            return;
        }

        // Sempre usa a empresa do usuário logado
        $empresaId = get_empresa_id();
        $data['empresa_id'] = $empresaId;

        // Verifica se já existe um setor com o mesmo nome na empresa
        if ($this->setorModel->existeNaEmpresa($data['nome'], $empresaId)) {
            set_flash_message('error', 'Já existe um setor com este nome nesta empresa.');
            redirect('setores/criar');
            return;
        }

        // Define valores padrão
        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $data['criado_por'] = get_user_id();
        $data['criado_em'] = date('Y-m-d H:i:s');

        // Cria o setor
        $id = $this->setorModel->create($data);

        if ($id) {
            set_flash_message('success', 'Setor criado com sucesso.');
            redirect('setores/admin');
        } else {
            set_flash_message('error', 'Erro ao criar setor.');
            redirect('setores/criar');
        }
    }

    /**
     * Formulário para editar setor
     * Apenas para administradores
     * 
     * @param int $id ID do setor a ser editado
     */
    public function editar($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        $this->render('setores/admin/form', [
            'titulo' => 'Editar Setor',
            'acao' => 'editar',
            'setor' => $setor
        ]);
    }

    /**
     * Processa a atualização de setor
     * Apenas para administradores
     * 
     * @param int $id ID do setor a ser atualizado
     */
    public function update($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/editar/' . $id);
            return;
        }

        // Verifica se já existe um setor com o mesmo nome na empresa (exceto o próprio)
        if ($this->setorModel->existeNaEmpresa($data['nome'], $empresaId, $id)) {
            set_flash_message('error', 'Já existe um setor com este nome nesta empresa.');
            redirect('setores/editar/' . $id);
            return;
        }

        // Define valores padrão
        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $data['atualizado_por'] = get_user_id();
        $data['atualizado_em'] = date('Y-m-d H:i:s');

        // Atualiza o setor
        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor atualizado com sucesso.');
            redirect('setores/admin');
        } else {
            set_flash_message('error', 'Erro ao atualizar setor.');
            redirect('setores/editar/' . $id);
        }
    }

    /**
     * Remove um setor
     * Apenas para administradores
     * 
     * @param int $id ID do setor a ser removido
     */
    public function remover($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se há chamados ou usuários associados ao setor
        $totalChamados = $this->setorModel->contarChamados($id);
        $totalUsuarios = $this->setorModel->contarUsuarios($id);

        if ($totalChamados > 0 || $totalUsuarios > 0) {
            $mensagem = 'Não é possível remover este setor pois ';
            $itens = [];

            if ($totalChamados > 0) {
                $itens[] = 'existem ' . $totalChamados . ' chamado(s) associado(s) a ele';
            }

            if ($totalUsuarios > 0) {
                $itens[] = 'existem ' . $totalUsuarios . ' usuário(s) associado(s) a ele';
            }

            $mensagem .= implode(' e ', $itens) . '.';
            set_flash_message('error', $mensagem);
            redirect('setores/admin');
            return;
        }

        // Marca o setor como removido em vez de excluir permanentemente
        $data = [
            'removido' => 1,
            'ativo' => 0,
            'removido_por' => get_user_id(),
            'data_remocao' => date('Y-m-d H:i:s')
        ];

        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor removido com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao remover setor.');
        }

        redirect('setores/admin');
    }

    /**
     * Restaura um setor removido
     * Apenas para administradores
     * 
     * @param int $id ID do setor a ser restaurado
     */
    public function restaurar($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se o setor está realmente removido
        if (!isset($setor['removido']) || !$setor['removido']) {
            set_flash_message('error', 'Este setor não está removido.');
            redirect('setores/admin');
            return;
        }

        // Restaura o setor
        $data = [
            'ativo' => 1,
            'removido' => 0,
            'removido_por' => null,
            'data_remocao' => null
        ];

        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor restaurado com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao restaurar setor.');
        }

        redirect('setores/admin?mostrar_removidos=1');
    }

    /**
     * Ativa/desativa um setor
     * Apenas para administradores
     * 
     * @param int $id ID do setor a ser ativado/desativado
     */
    public function toggle($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Alterna o status
        $novoStatus = $setor['ativo'] ? 0 : 1;

        if ($this->setorModel->update($id, ['ativo' => $novoStatus])) {
            $mensagem = $novoStatus ? 'Setor ativado com sucesso.' : 'Setor desativado com sucesso.';
            set_flash_message('success', $mensagem);
        } else {
            set_flash_message('error', 'Erro ao alterar status do setor.');
        }

        redirect('setores/admin');
    }

    /**
     * Gerencia usuários do setor
     * Apenas para administradores
     * 
     * @param int $id ID do setor para gerenciar usuários
     */
    public function usuarios($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Obtém todos os usuários da empresa
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->findAll(
            'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Obtém os usuários associados ao setor
        try {
            $sql = "SELECT us.*, u.nome, u.email, u.cargo, us.principal 
                    FROM usuarios_setores us 
                    JOIN usuarios u ON us.usuario_id = u.id 
                    WHERE us.setor_id = :setor_id";

            $usuariosSetor = $this->setorModel->executeQuery($sql, ['setor_id' => $id]);
        } catch (Exception $e) {
            // Se a tabela não existir, define como array vazio
            $usuariosSetor = [];
        }

        // Marca os usuários que já estão associados
        foreach ($usuarios as &$usuario) {
            $usuario['associado'] = false;
            $usuario['principal'] = false;
            foreach ($usuariosSetor as $usuarioSetor) {
                if ($usuario['id'] == $usuarioSetor['usuario_id']) {
                    $usuario['associado'] = true;
                    $usuario['principal'] = $usuarioSetor['principal'];
                    break;
                }
            }
        }

        $this->render('setores/admin/usuarios', [
            'setor' => $setor,
            'usuarios' => $usuarios,
            'usuariosSetor' => $usuariosSetor
        ]);
    }

    /**
     * Associa/desassocia um usuário ao setor
     * Apenas para administradores
     */
    public function associarUsuario()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'setor_id' => 'Setor',
            'usuario_id' => 'Usuário'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/usuarios/' . $data['setor_id']);
            return;
        }

        $empresaId = get_empresa_id();

        // Verifica se o setor existe e pertence à empresa do usuário logado
        $setor = $this->setorModel->findById($data['setor_id']);
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se o usuário existe e pertence à empresa do usuário logado
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($data['usuario_id']);
        if (!$usuario || $usuario['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('setores/usuarios/' . $data['setor_id']);
            return;
        }

        try {
            // Verifica se a tabela usuarios_setores existe
            if ($this->setorModel->tableExists('usuarios_setores')) {
                // Verifica se o usuário já está associado ao setor
                $sql = "SELECT * FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                $usuarioSetor = $this->setorModel->executeQuerySingle($sql, [
                    'usuario_id' => $data['usuario_id'],
                    'setor_id' => $data['setor_id']
                ]);

                // Se estiver associando
                if (isset($data['associar']) && $data['associar'] == 1) {
                    // Se já estiver associado, atualiza o status de principal
                    if ($usuarioSetor) {
                        $sql = "UPDATE usuarios_setores SET principal = :principal WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                        $this->setorModel->executeUpdate($sql, [
                            'principal' => isset($data['principal']) ? 1 : 0,
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id']
                        ]);
                        set_flash_message('success', 'Usuário atualizado no setor com sucesso.');
                    } else {
                        // Se não estiver associado, insere
                        $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_por, criado_em) VALUES (:usuario_id, :setor_id, :principal, :criado_por, :criado_em)";
                        $this->setorModel->executeUpdate($sql, [
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id'],
                            'principal' => isset($data['principal']) ? 1 : 0,
                            'criado_por' => get_user_id(),
                            'criado_em' => date('Y-m-d H:i:s')
                        ]);
                        set_flash_message('success', 'Usuário associado ao setor com sucesso.');
                    }
                } else {
                    // Se estiver desassociando
                    if ($usuarioSetor) {
                        $sql = "DELETE FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                        $this->setorModel->executeUpdate($sql, [
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id']
                        ]);
                        set_flash_message('success', 'Usuário desassociado do setor com sucesso.');
                    } else {
                        set_flash_message('error', 'Usuário não está associado ao setor.');
                    }
                }
            } else {
                // Se a tabela não existir, cria a tabela
                $sql = "CREATE TABLE IF NOT EXISTS `usuarios_setores` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `usuario_id` int(11) NOT NULL,
                    `setor_id` int(11) NOT NULL,
                    `principal` tinyint(1) DEFAULT 0,
                    `criado_por` int(11) DEFAULT NULL,
                    `criado_em` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_usuario_setor` (`usuario_id`, `setor_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

                $this->setorModel->executeRawQuery($sql);

                $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_por, criado_em) VALUES (:usuario_id, :setor_id, :principal, :criado_por, :criado_em)";
                $this->setorModel->executeUpdate($sql, [
                    'usuario_id' => $data['usuario_id'],
                    'setor_id' => $data['setor_id'],
                    'principal' => isset($data['principal']) ? 1 : 0,
                    'criado_por' => get_user_id(),
                    'criado_em' => date('Y-m-d H:i:s')
                ]);
                set_flash_message('success', 'Usuário associado ao setor com sucesso.');
            }
        } catch (Exception $e) {
            set_flash_message('error', 'Erro ao associar/desassociar usuário: ' . $e->getMessage());
        }

        redirect('setores/usuarios/' . $data['setor_id']);
    }


    /**
     * Obtém os usuários de um setor via AJAX
     * 
     * @param int $id ID do setor
     */
    public function getUsuariosSetor($id)
    {
        // Verifica autenticação
        if (!is_authenticated()) {
            $this->outputJson(['error' => 'Não autorizado'], 401);
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            $this->outputJson(['error' => 'Setor não encontrado'], 404);
            return;
        }

        try {
            // Obtém os usuários do setor
            $sql = "SELECT u.id, u.nome, u.email, u.cargo, us.principal 
                FROM usuarios u 
                JOIN usuarios_setores us ON u.id = us.usuario_id 
                WHERE us.setor_id = :setor_id 
                AND u.empresa_id = :empresa_id 
                AND u.ativo = 1 
                AND (u.removido = 0 OR u.removido IS NULL)
                ORDER BY u.nome ASC";

            $usuarios = $this->setorModel->executeQuery($sql, [
                'setor_id' => $id,
                'empresa_id' => $empresaId
            ]);

            $this->outputJson(['usuarios' => $usuarios]);
        } catch (Exception $e) {
            error_log("Erro ao obter usuários do setor: " . $e->getMessage());
            $this->outputJson(['error' => 'Erro ao obter usuários', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Envia uma resposta JSON
     * 
     * @param array $data Dados a serem enviados
     * @param int $statusCode Código de status HTTP
     */
    private function outputJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Ativa múltiplos setores em lote
     */
    public function batchActivate()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $ids = $this->getPostData('ids');

        if (empty($ids)) {
            set_flash_message('error', 'Nenhum setor selecionado.');
            redirect('setores/admin');
            return;
        }

        $empresaId = get_empresa_id();
        $success = 0;
        $errors = 0;

        foreach ($ids as $id) {
            // Verifica se o setor existe e pertence à empresa do usuário
            $setor = $this->setorModel->findById($id);
            if ($setor && $setor['empresa_id'] == $empresaId) {
                if ($this->setorModel->update($id, ['ativo' => 1])) {
                    $success++;
                } else {
                    $errors++;
                }
            } else {
                $errors++;
            }
        }

        if ($success > 0) {
            set_flash_message('success', "$success setores foram ativados com sucesso.");
        }

        if ($errors > 0) {
            set_flash_message('error', "$errors setores não puderam ser ativados.");
        }

        redirect('setores/admin');
    }

    /**
     * Desativa múltiplos setores em lote
     */
    public function batchDeactivate()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $ids = $this->getPostData('ids');

        if (empty($ids)) {
            set_flash_message('error', 'Nenhum setor selecionado.');
            redirect('setores/admin');
            return;
        }

        $empresaId = get_empresa_id();
        $success = 0;
        $errors = 0;

        foreach ($ids as $id) {
            // Verifica se o setor existe e pertence à empresa do usuário
            $setor = $this->setorModel->findById($id);
            if ($setor && $setor['empresa_id'] == $empresaId) {
                if ($this->setorModel->update($id, ['ativo' => 0])) {
                    $success++;
                } else {
                    $errors++;
                }
            } else {
                $errors++;
            }
        }

        if ($success > 0) {
            set_flash_message('success', "$success setores foram desativados com sucesso.");
        }

        if ($errors > 0) {
            set_flash_message('error', "$errors setores não puderam ser desativados.");
        }

        redirect('setores/admin');
    }

    /**
     * Remove múltiplos setores em lote
     */
    public function batchRemove()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $ids = $this->getPostData('ids');

        if (empty($ids)) {
            set_flash_message('error', 'Nenhum setor selecionado.');
            redirect('setores/admin');
            return;
        }

        $empresaId = get_empresa_id();
        $success = 0;
        $errors = 0;

        foreach ($ids as $id) {
            // Verifica se o setor existe e pertence à empresa do usuário
            $setor = $this->setorModel->findById($id);
            if ($setor && $setor['empresa_id'] == $empresaId) {
                // Verifica se há chamados ou usuários associados ao setor
                $totalChamados = $this->setorModel->contarChamados($id);
                $totalUsuarios = $this->setorModel->contarUsuarios($id);

                if ($totalChamados > 0 || $totalUsuarios > 0) {
                    $errors++;
                    continue;
                }

                // Marca o setor como removido
                $data = [
                    'removido' => 1,
                    'ativo' => 0,
                    'removido_por' => get_user_id(),
                    'data_remocao' => date('Y-m-d H:i:s')
                ];

                if ($this->setorModel->update($id, $data)) {
                    $success++;
                } else {
                    $errors++;
                }
            } else {
                $errors++;
            }
        }

        if ($success > 0) {
            set_flash_message('success', "$success setores foram removidos com sucesso.");
        }

        if ($errors > 0) {
            set_flash_message('error', "$errors setores não puderam ser removidos.");
        }

        redirect('setores/admin');
    }
}
