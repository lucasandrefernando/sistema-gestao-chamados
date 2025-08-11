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
     * ✅ HELPER: Gera iniciais corretas do nome
     */
    private function getIniciais($nomeCompleto)
    {
        if (empty($nomeCompleto)) return '?';

        $nomes = array_filter(explode(' ', trim($nomeCompleto)), function ($nome) {
            return !empty($nome);
        });

        if (count($nomes) === 0) return '?';

        if (count($nomes) === 1) {
            // Se só tem um nome, pega as duas primeiras letras
            return strtoupper(substr($nomes[0], 0, 2));
        } else {
            // Se tem mais de um nome, pega primeira letra do primeiro e último nome
            return strtoupper($nomes[0][0] . $nomes[count($nomes) - 1][0]);
        }
    }

    /**
     * Função auxiliar para formatar o tempo decorrido desde uma data
     */
    public function time_elapsed_string($datetime, $full = false)
    {
        if (empty($datetime)) {
            return 'Nunca';
        }

        try {
            $now = new DateTime();
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            // Calculamos semanas manualmente, pois DateInterval não tem propriedade 'w'
            $weeks = floor($diff->days / 7);
            $days_remaining = $diff->days % 7;

            $string = array();

            // Anos
            if ($diff->y > 0) {
                $string['y'] = $diff->y . ' ' . ($diff->y > 1 ? 'anos' : 'ano');
            }

            // Meses
            if ($diff->m > 0) {
                $string['m'] = $diff->m . ' ' . ($diff->m > 1 ? 'meses' : 'mês');
            }

            // Semanas (calculadas manualmente)
            if ($weeks > 0) {
                $string['w'] = $weeks . ' ' . ($weeks > 1 ? 'semanas' : 'semana');
            }

            // Dias (restantes após cálculo de semanas)
            if ($days_remaining > 0) {
                $string['d'] = $days_remaining . ' ' . ($days_remaining > 1 ? 'dias' : 'dia');
            }

            // Horas
            if ($diff->h > 0) {
                $string['h'] = $diff->h . ' ' . ($diff->h > 1 ? 'horas' : 'hora');
            }

            // Minutos
            if ($diff->i > 0) {
                $string['i'] = $diff->i . ' ' . ($diff->i > 1 ? 'minutos' : 'minuto');
            }

            // Segundos
            if ($diff->s > 0) {
                $string['s'] = $diff->s . ' ' . ($diff->s > 1 ? 'segundos' : 'segundo');
            }

            // Se não tiver nenhum valor, é "agora"
            if (empty($string)) {
                return 'agora';
            }

            // Se não for full, pega só o primeiro valor
            if (!$full) {
                $string = array_slice($string, 0, 1);
            }

            return 'há ' . implode(', ', $string);
        } catch (Exception $e) {
            // Em caso de erro na data, retorna um valor padrão
            return 'Data inválida';
        }
    }

    /**
     * ✅ MÉTODO COMPLETO: FILTROS + MODAIS AJAX
     */
    public function index()
    {
        $empresaId = get_empresa_id();

        // ✅ RESPOSTA AJAX PARA MODAIS
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');

            if ($_GET['ajax'] === 'licencas_utilizadas') {
                try {
                    $sql = "SELECT * FROM usuarios WHERE empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL) ORDER BY nome ASC";
                    $stmt = $this->usuarioModel->getDb()->prepare($sql);
                    $stmt->bindValue(':empresa_id', $empresaId);
                    $stmt->execute();
                    $usuariosAtivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Adiciona iniciais para cada usuário
                    foreach ($usuariosAtivos as &$usuario) {
                        $usuario['iniciais'] = $this->getIniciais($usuario['nome']);
                    }

                    echo json_encode(['usuarios' => $usuariosAtivos]);
                } catch (Exception $e) {
                    error_log("Erro ao buscar licenças utilizadas: " . $e->getMessage());
                    echo json_encode(['usuarios' => [], 'error' => 'Erro ao carregar dados']);
                }
                exit;
            }

            if ($_GET['ajax'] === 'usuarios_online') {
                try {
                    $usuariosOnline = $this->usuarioModel->getUsuariosOnline($empresaId, 50);

                    // Adiciona iniciais para cada usuário online
                    if (isset($usuariosOnline['usuarios'])) {
                        foreach ($usuariosOnline['usuarios'] as &$usuario) {
                            $usuario['iniciais'] = $this->getIniciais($usuario['nome']);

                            // Calcula tempo online se disponível
                            if (isset($usuario['session_start'])) {
                                $usuario['tempo_online'] = $this->time_elapsed_string($usuario['session_start']);
                            }
                        }
                    }

                    echo json_encode($usuariosOnline);
                } catch (Exception $e) {
                    error_log("Erro ao buscar usuários online: " . $e->getMessage());
                    echo json_encode(['usuarios' => [], 'total' => 0, 'error' => 'Erro ao carregar dados']);
                }
                exit;
            }
        }

        $mostrarRemovidos = isset($_GET['mostrar_removidos']) && $_GET['mostrar_removidos'] == 1;

        // ✅ FILTROS
        $filtros = [
            'busca' => isset($_GET['busca']) ? trim($_GET['busca']) : '',
            'status' => isset($_GET['status']) ? $_GET['status'] : 'all',
            'tipo' => isset($_GET['tipo']) ? $_GET['tipo'] : 'all'
        ];

        $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $registrosPorPagina = 4;

        // ✅ MONTA CONDIÇÕES SEM PLACEHOLDERS PROBLEMÁTICOS
        $condicoes = [];

        // Filtro de remoção
        if (!$mostrarRemovidos) {
            $condicoes[] = '(removido = 0 OR removido IS NULL)';
        }

        // ✅ FILTRO DE BUSCA
        if (!empty($filtros['busca'])) {
            $busca = $this->usuarioModel->getDb()->quote('%' . $filtros['busca'] . '%');
            $condicoes[] = "(nome LIKE {$busca} OR email LIKE {$busca})";
        }

        // ✅ FILTRO POR STATUS
        if ($filtros['status'] !== 'all') {
            if ($filtros['status'] === 'active') {
                $condicoes[] = 'ativo = 1';
            } elseif ($filtros['status'] === 'inactive') {
                $condicoes[] = 'ativo = 0';
            } elseif ($filtros['status'] === 'removed') {
                $condicoes[] = 'removido = 1';
            }
        }

        // ✅ FILTRO POR TIPO
        if ($filtros['tipo'] !== 'all') {
            if ($filtros['tipo'] === 'admin') {
                $condicoes[] = 'admin = 1';
            } elseif ($filtros['tipo'] === 'regular') {
                $condicoes[] = 'admin = 0';
            }
        }

        $condicaoFinal = !empty($condicoes) ? implode(' AND ', $condicoes) : '';

        // ✅ BUSCA USUÁRIOS (SEM PARÂMETROS PROBLEMÁTICOS)
        $resultado = $this->usuarioModel->findByEmpresaPaginado(
            $empresaId,
            $paginaAtual,
            $registrosPorPagina,
            $condicaoFinal,
            [] // Array vazio - não usa parâmetros
        );

        $usuarios = $resultado['usuarios'];
        $paginacao = $resultado['paginacao'];

        // Formata tempo decorrido e adiciona iniciais
        foreach ($usuarios as &$usuario) {
            if (isset($usuario['ultimo_acesso']) && $usuario['ultimo_acesso']) {
                $usuario['tempo_decorrido'] = $this->time_elapsed_string($usuario['ultimo_acesso']);
            }

            // ✅ ADICIONA INICIAIS CORRETAS
            $usuario['iniciais'] = $this->getIniciais($usuario['nome']);
        }

        // ✅ LICENÇAS (MÉTODO SIMPLES)
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();
        $totalLicencas = $licencaModel->getTotalLicencas($empresaId);

        $sqlAtivos = "SELECT COUNT(*) FROM usuarios WHERE empresa_id = {$empresaId} AND ativo = 1 AND (removido = 0 OR removido IS NULL)";
        $stmtAtivos = $this->usuarioModel->getDb()->prepare($sqlAtivos);
        $stmtAtivos->execute();
        $totalUsuariosAtivos = (int) $stmtAtivos->fetchColumn();

        $licencasInfo = [
            'total' => $totalLicencas,
            'utilizadas' => $totalUsuariosAtivos,
            'disponiveis' => $totalLicencas - $totalUsuariosAtivos
        ];

        $usuariosOnlineInfo = $this->usuarioModel->getUsuariosOnline($empresaId);

        $this->render('usuarios/index', [
            'usuarios' => $usuarios,
            'mostrarRemovidos' => $mostrarRemovidos,
            'licencasInfo' => $licencasInfo,
            'paginacao' => $paginacao,
            'usuariosOnlineInfo' => $usuariosOnlineInfo,
            'filtros' => $filtros
        ]);
    }

    /**
     * Formulário para criar usuário 
     */
    public function criar()
    {
        $empresaId = get_empresa_id();

        // Verifica se há licenças disponíveis
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();
        $totalLicencas = $licencaModel->getTotalLicencas($empresaId);

        // Conta o número de usuários ativos da empresa
        $totalUsuarios = $this->usuarioModel->count('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId]);

        // Se não houver licenças disponíveis
        if ($totalLicencas <= $totalUsuarios) {
            // Se for admin master, redireciona para criar licença
            if (is_admin_master()) {
                set_flash_message('warning', 'Não há licenças disponíveis para criar novos usuários. Por favor, crie uma nova licença primeiro.');
                redirect('licencas/criar');
                return;
            } else {
                // Se for admin regular, exibe a mensagem na página de usuários
                set_flash_message('warning', 'Não há licenças disponíveis para criar novos usuários. Por favor, solicite ao administrador master que crie novas licenças.');
                redirect('usuarios');
                return;
            }
        }

        $this->render('usuarios/form', [
            'titulo' => 'Novo Usuário',
            'acao' => 'criar'
        ]);
    }

    /**
     * Remove um usuário
     */
    public function remover($id)
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

        // Impede a remoção do próprio usuário
        if ($usuario['id'] == get_user_id()) {
            set_flash_message('error', 'Você não pode remover seu próprio usuário.');
            redirect('usuarios');
            return;
        }

        // Verifica permissões para remover usuários
        if (!is_admin_master()) {
            // Administradores regulares não podem remover administradores
            if ($usuario['admin'] == 1) {
                set_flash_message('error', 'Você não tem permissão para remover usuários administradores. Apenas administradores master podem fazer isso.');
                redirect('usuarios');
                return;
            }
        } else {
            // Mesmo administradores master não podem remover outros administradores master
            if ($usuario['admin'] == 1 && isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master' && $usuario['id'] != get_user_id()) {
                // Verificação adicional: se o usuário atual é o criador do admin master, pode remover
                // Isso é opcional e depende da sua lógica de negócio
                $criadorId = $usuario['criado_por'] ?? null;
                if ($criadorId != get_user_id()) {
                    set_flash_message('error', 'Você não pode remover outro administrador master.');
                    redirect('usuarios');
                    return;
                }
            }
        }

        // Marca o usuário como removido em vez de excluir permanentemente
        $data = [
            'removido' => 1,
            'ativo' => 0,
            'data_remocao' => date('Y-m-d H:i:s')
        ];

        if ($this->usuarioModel->update($id, $data)) {
            set_flash_message('success', 'Usuário removido com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao remover usuário.');
        }

        redirect('usuarios');
    }

    /**
     * Processa a criação de usuário
     */
    public function store()
    {
        // Obtém os dados do formulário
        $data = $this->getPostData();

        // --- INÍCIO DAS MUDANÇAS PARA NOME/SOBRENOME E CONFIRMAR SENHA ---

        // 1. Valida os campos obrigatórios, incluindo 'sobrenome' e 'confirmar_senha'
        $requiredFields = [
            'nome' => 'Nome',
            'sobrenome' => 'Sobrenome', // Adicionado
            'email' => 'E-mail',
            'senha' => 'Senha',
            'confirmar_senha' => 'Confirmação de Senha' // Adicionado
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        // 2. Verifica se as senhas coincidem (se ambas foram fornecidas)
        if (isset($data['senha']) && isset($data['confirmar_senha']) && $data['senha'] !== $data['confirmar_senha']) {
            $errors[] = 'A senha e a confirmação de senha não coincidem.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('usuarios/criar');
            return;
        }

        // 3. Concatena nome e sobrenome na coluna 'nome'
        $data['nome'] = trim($data['nome'] . ' ' . $data['sobrenome']);

        // 4. Remove o campo 'sobrenome' e 'confirmar_senha' antes de enviar para o modelo
        unset($data['sobrenome']);
        unset($data['confirmar_senha']);

        // --- FIM DAS MUDANÇAS PARA NOME/SOBRENOME E CONFIRMAR SENHA ---

        // Sempre usa a empresa do usuário logado
        $empresaId = get_empresa_id();
        $data['empresa_id'] = $empresaId;

        // Verifica se o e-mail já existe na mesma empresa (incluindo removidos)
        $existente = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id', [
            'email' => $data['email'],
            'empresa_id' => $empresaId
        ]);

        // Se existir e estiver removido, armazena informações na sessão e redireciona
        if ($existente && isset($existente['removido']) && $existente['removido']) {
            // Armazena informações do usuário removido na sessão para exibição na view
            $_SESSION['usuario_removido_encontrado'] = true;
            $_SESSION['usuario_removido_id'] = $existente['id'];
            $_SESSION['usuario_removido_nome'] = $existente['nome'];
            $_SESSION['usuario_removido_email'] = $existente['email'];
            $_SESSION['usuario_removido_data'] = $existente['data_remocao'];

            // Redireciona para a página de usuários onde será exibido o alerta
            redirect('usuarios');
            return;
        }

        // Se existir e não estiver removido, exibe mensagem de erro
        if ($existente && (!isset($existente['removido']) || !$existente['removido'])) {
            set_flash_message('error', 'Este e-mail já está em uso nesta empresa.');
            redirect('usuarios/criar');
            return;
        }

        // Define valores padrão
        $data['admin'] = isset($data['admin']) ? 1 : 0;
        $data['ativo'] = 1;

        // Verifica se o usuário atual é admin master
        if (is_admin_master()) {
            // Se for admin master, pode definir o tipo de admin
            if ($data['admin'] == 1) {
                $data['admin_tipo'] = isset($data['admin_tipo']) ? $data['admin_tipo'] : 'regular';
            } else {
                $data['admin_tipo'] = null;
            }
        } else {
            // Se não for admin master, só pode criar admin regular
            if ($data['admin'] == 1) {
                // Verifica se está tentando criar um admin master
                if (isset($data['admin_tipo']) && $data['admin_tipo'] == 'master') {
                    set_flash_message('error', 'Você não tem permissão para criar administradores master. Apenas administradores master podem criar outros administradores master.');
                    redirect('usuarios/criar');
                    return;
                }
                $data['admin_tipo'] = 'regular';
            } else {
                $data['admin_tipo'] = null;
            }
        }

        // Verifica se há licenças disponíveis
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();
        $totalLicencas = $licencaModel->getTotalLicencas($empresaId);
        $totalUsuarios = $this->usuarioModel->count('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId]);

        if ($totalLicencas <= $totalUsuarios) {
            if (is_admin_master()) {
                set_flash_message('warning', 'Não há licenças disponíveis para criar novos usuários. Por favor, crie uma nova licença primeiro.');
                redirect('licencas/criar');
            } else {
                set_flash_message('warning', 'Não há licenças disponíveis para criar novos usuários. Por favor, solicite ao administrador master que crie novas licenças.');
                redirect('usuarios');
            }
            return;
        }

        // Hash da senha
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

        // Cria o usuário
        $id = $this->usuarioModel->create($data);

        if ($id) {
            set_flash_message('success', 'Usuário criado com sucesso.');
            redirect('usuarios');
        } else {
            set_flash_message('error', 'Erro ao criar usuário.');
            redirect('usuarios/criar');
        }
    }

    /**
     * Confirma a restauração de um usuário removido
     * Exibe uma página de confirmação com detalhes do usuário antes de restaurá-lo
     */
    public function confirmarRestauracao($id)
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

        // Verifica se o usuário está realmente removido
        if (!isset($usuario['removido']) || !$usuario['removido']) {
            set_flash_message('error', 'Este usuário não está removido.');
            redirect('usuarios');
            return;
        }

        // Verifica permissões para restaurar usuários
        if (!is_admin_master() && $usuario['admin'] == 1) {
            set_flash_message('error', 'Você não tem permissão para restaurar usuários administradores. Apenas administradores master podem fazer isso.');
            redirect('usuarios?mostrar_removidos=1');
            return;
        }

        // Verifica se há licenças disponíveis
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();
        $totalLicencas = $licencaModel->getTotalLicencas($empresaId);

        // Conta o número de usuários ativos da empresa
        $totalUsuarios = $this->usuarioModel->count('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId]);

        // Prepara informações de licenças para a view
        $licencasInfo = [
            'total' => $totalLicencas,
            'utilizadas' => $totalUsuarios,
            'disponiveis' => $totalLicencas - $totalUsuarios
        ];

        // Renderiza a página de confirmação de restauração
        $this->render('usuarios/confirmar-restauracao', [
            'usuario' => $usuario,
            'licencasInfo' => $licencasInfo
        ]);
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

        // Se o usuário tem nome completo (nome + sobrenome), tenta separar para o formulário
        if (isset($usuario['nome'])) {
            $partesNome = explode(' ', $usuario['nome'], 2); // Divide em no máximo 2 partes
            $usuario['nome_primeiro'] = $partesNome[0];
            $usuario['sobrenome'] = isset($partesNome[1]) ? $partesNome[1] : '';
        } else {
            $usuario['nome_primeiro'] = '';
            $usuario['sobrenome'] = '';
        }


        $this->render('usuarios/form', [
            'titulo' => 'Editar Usuário',
            'acao' => 'editar',
            'usuario' => $usuario
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

        // --- INÍCIO DAS MUDANÇAS PARA NOME/SOBRENOME E CONFIRMAR SENHA ---

        // 1. Valida os campos obrigatórios, incluindo 'sobrenome'
        $requiredFields = [
            'nome' => 'Nome',
            'sobrenome' => 'Sobrenome', // Adicionado
            'email' => 'E-mail'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        // 2. Verifica se a nova senha e a confirmação de senha coincidem (se ambas foram fornecidas)
        if (!empty($data['senha']) && isset($data['confirmar_senha']) && $data['senha'] !== $data['confirmar_senha']) {
            $errors[] = 'A nova senha e a confirmação de senha não coincidem.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('usuarios/editar/' . $id);
            return;
        }

        // 3. Concatena nome e sobrenome na coluna 'nome'
        $data['nome'] = trim($data['nome'] . ' ' . $data['sobrenome']);

        // 4. Remove o campo 'sobrenome' e 'confirmar_senha' antes de enviar para o modelo
        unset($data['sobrenome']);
        unset($data['confirmar_senha']);

        // --- FIM DAS MUDANÇAS PARA NOME/SOBRENOME E CONFIRMAR SENHA ---

        // Verifica se o e-mail já existe NA MESMA EMPRESA (exceto para o próprio usuário)
        $existente = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND id != :id', [
            'email' => $data['email'],
            'empresa_id' => $empresaId,
            'id' => $id
        ]);

        if ($existente) {
            set_flash_message('error', 'Este e-mail já está em uso por outro usuário nesta empresa.');
            redirect('usuarios/editar/' . $id);
            return;
        }

        // Define valores padrão
        $data['admin'] = isset($data['admin']) ? 1 : 0;

        // Sempre usa a empresa do usuário logado
        $data['empresa_id'] = $empresaId;

        // Verifica se o usuário atual é admin master
        if (is_admin_master()) {
            // Se for admin master, pode definir o tipo de admin
            if ($data['admin'] == 1) {
                $data['admin_tipo'] = isset($data['admin_tipo']) ? $data['admin_tipo'] : 'regular';
            } else {
                $data['admin_tipo'] = null;
            }
        } else {
            // Se não for admin master
            if ($data['admin'] == 1) {
                // Se o usuário já era admin master, mantém como master
                if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master') {
                    $data['admin_tipo'] = 'master';
                } else {
                    // Verifica se está tentando promover a admin master
                    if (isset($data['admin_tipo']) && $data['admin_tipo'] == 'master') {
                        set_flash_message('error', 'Você não tem permissão para promover usuários a administradores master. Apenas administradores master podem fazer isso.');
                        redirect('usuarios/editar/' . $id);
                        return;
                    }
                    $data['admin_tipo'] = 'regular';
                }
            } else {
                // Se o usuário era admin master e está tentando remover o status de admin
                if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master') {
                    set_flash_message('error', 'Você não tem permissão para remover o status de administrador de um administrador master. Apenas administradores master podem fazer isso.');
                    redirect('usuarios/editar/' . $id);
                    return;
                }
                $data['admin_tipo'] = null;
            }
        }

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
     * Restaura um usuário removido
     */
    public function restaurar($id)
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

        // Verifica se o usuário está realmente removido
        if (!isset($usuario['removido']) || !$usuario['removido']) {
            set_flash_message('error', 'Este usuário não está removido.');
            redirect('usuarios');
            return;
        }

        // Verifica permissões para restaurar usuários
        if (!is_admin_master() && $usuario['admin'] == 1) {
            set_flash_message('error', 'Você não tem permissão para restaurar usuários administradores. Apenas administradores master podem fazer isso.');
            redirect('usuarios?mostrar_removidos=1');
            return;
        }

        // Verifica se há licenças disponíveis
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();
        $totalLicencas = $licencaModel->getTotalLicencas($empresaId);

        // Conta o número de usuários ativos da empresa
        $totalUsuarios = $this->usuarioModel->count('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId]);

        // Se não houver licenças disponíveis
        if ($totalLicencas <= $totalUsuarios) {
            // Se for admin master, redireciona para criar licença
            if (is_admin_master()) {
                set_flash_message('warning', 'Não há licenças disponíveis para restaurar este usuário. Por favor, crie uma nova licença primeiro.');
                redirect('licencas/criar');
                return;
            } else {
                // Se for admin regular, exibe a mensagem na página de usuários
                set_flash_message('warning', 'Não há licenças disponíveis para restaurar este usuário. Por favor, solicite ao administrador master que crie novas licenças.');
                redirect('usuarios?mostrar_removidos=1');
                return;
            }
        }

        // Restaura o usuário
        $data = [
            'ativo' => 1,
            'removido' => 0,
            'data_remocao' => null
        ];

        if ($this->usuarioModel->update($id, $data)) {
            set_flash_message('success', 'Usuário restaurado com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao restaurar usuário.');
        }

        redirect('usuarios?mostrar_removidos=1');
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

        // Impede a desativação do próprio usuário
        if ($usuario['id'] == get_user_id()) {
            set_flash_message('error', 'Você não pode desativar seu próprio usuário.');
            redirect('usuarios');
            return;
        }

        // Verifica permissões para ativar/desativar usuários
        if (!is_admin_master()) {
            // Administradores regulares não podem ativar/desativar administradores
            if ($usuario['admin'] == 1) {
                set_flash_message('error', 'Você não tem permissão para ativar/desativar usuários administradores. Apenas administradores master podem fazer isso.');
                redirect('usuarios');
                return;
            }
        } else {
            // Mesmo administradores master não podem desativar outros administradores master
            if ($usuario['admin'] == 1 && isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master' && $usuario['id'] != get_user_id()) {
                // Verificação adicional: se o usuário atual é o criador do admin master, pode desativar
                $criadorId = $usuario['criado_por'] ?? null;
                if ($criadorId != get_user_id()) {
                    set_flash_message('error', 'Você não pode ativar/desativar outro administrador master.');
                    redirect('usuarios');
                    return;
                }
            }
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

    /**
     * Força o logout de um usuário
     */
    public function forcarLogout($id)
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

        // Verifica se o usuário tem uma sessão ativa
        if (empty($usuario['session_id'])) {
            set_flash_message('info', 'Este usuário não está logado atualmente.');
            redirect('usuarios');
            return;
        }

        // Limpa a sessão do usuário
        if ($this->usuarioModel->limparSessao($id)) {
            // Registra a ação no log
            error_log("Sessão do usuário ID: {$id} foi encerrada forçadamente pelo usuário ID: " . get_user_id());

            set_flash_message('success', 'Sessão do usuário encerrada com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao encerrar sessão do usuário.');
        }

        redirect('usuarios');
    }
}
