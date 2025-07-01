<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/Empresa.php';
require_once ROOT_DIR . '/app/models/EmailService.php';

/**
 * Controlador para autenticação
 */
class AuthController extends Controller
{
    private $usuarioModel;
    private $empresaModel;
    private $emailService;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
        $this->emailService = new EmailService();
    }

    /**
     * Exibe o formulário de login
     */
    public function index()
    {
        // Se já estiver autenticado, redireciona para o dashboard
        if (is_authenticated()) {
            redirect('dashboard');
            return;
        }

        // Obtém a lista de empresas ativas para o dropdown
        $empresas = $this->empresaModel->findAtivas();

        $this->render('auth/login', [
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa o login
     */
    public function login()
    {
        // Verifica se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth');
            return;
        }

        $email = sanitize_input($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $empresaId = intval($_POST['empresa_id'] ?? 0);
        $isAdminMaster = isset($_POST['is_admin_master']) && $_POST['is_admin_master'] === 'true';
        $forcarLogin = isset($_POST['forcar_login']) && $_POST['forcar_login'] === 'true';

        // Log para depuração
        error_log("Tentativa de login - Email: $email, Empresa ID: $empresaId, Admin Master: " . ($isAdminMaster ? 'Sim' : 'Não'));

        // Valida os campos
        $errors = [];

        if (empty($email)) {
            $errors[] = 'O campo E-mail é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O formato do e-mail é inválido.';
        }

        if (empty($senha)) {
            $errors[] = 'O campo Senha é obrigatório.';
        }

        // Só valida empresa_id se não for admin master
        if (!$isAdminMaster && $empresaId <= 0) {
            $errors[] = 'O campo Empresa é obrigatório.';
        }

        if (!empty($errors)) {
            error_log("Erro de validação: " . implode(', ', $errors));
            set_flash_message('error', implode('<br>', $errors));
            redirect('auth');
            return;
        }

        // Primeiro, verifica se o usuário existe em qualquer empresa
        $usuarioGlobal = $this->usuarioModel->findOne('email = :email AND ativo = 1', ['email' => $email]);

        // Se não existe, já retorna erro
        if (!$usuarioGlobal) {
            error_log("Usuário não encontrado: $email");

            // Adiciona um pequeno atraso para dificultar ataques de força bruta
            sleep(1);

            set_flash_message('error', 'E-mail ou senha inválidos.');
            redirect('auth');
            return;
        }

        // Verifica se é um admin master
        $isAdminMaster = $usuarioGlobal['admin'] == 1 && $usuarioGlobal['admin_tipo'] == 'master';

        // Se for admin master, pode logar sem verificar empresa
        if ($isAdminMaster) {
            // Verifica a senha
            if ($this->usuarioModel->verificarSenha($senha, $usuarioGlobal['senha'])) {
                // Verifica se o usuário já tem uma sessão ativa
                $sessaoAtiva = $this->usuarioModel->verificarSessaoAtiva($usuarioGlobal['id']);

                if ($sessaoAtiva && !$forcarLogin) {
                    // Armazena informações temporárias para a página de confirmação
                    $_SESSION['temp_login'] = [
                        'user_id' => $usuarioGlobal['id'],
                        'email' => $email,
                        'senha' => $senha,
                        'empresa_id' => $usuarioGlobal['empresa_id'],
                        'is_admin_master' => true,
                        'sessao_ativa' => $sessaoAtiva
                    ];

                    redirect('auth/confirmar_sessao');
                    return;
                }

                // Se não tem sessão ativa ou está forçando o login
                if ($forcarLogin) {
                    // Limpa a sessão anterior
                    $this->usuarioModel->limparSessao($usuarioGlobal['id']);

                    // Pequeno atraso para garantir que a sessão anterior seja encerrada
                    sleep(1);
                }

                // Se for admin master, usa a empresa do usuário como referência
                $empresaId = $usuarioGlobal['empresa_id'];
                $empresa = $this->empresaModel->findById($empresaId);

                // Registra o login bem-sucedido
                $this->registrarLoginSucesso($usuarioGlobal, $empresaId, $empresa);
                return;
            }
        } else {
            // Se não for admin master, só pode logar na própria empresa
            // Verifica se a empresa existe e está ativa
            if ($empresaId > 0) {
                $empresa = $this->empresaModel->findById($empresaId);
                if (!$empresa || !$empresa['ativo']) {
                    error_log("Empresa inválida ou inativa: $empresaId");
                    set_flash_message('error', 'Empresa inválida ou inativa.');
                    redirect('auth');
                    return;
                }

                $usuario = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND ativo = 1', [
                    'email' => $email,
                    'empresa_id' => $empresaId
                ]);

                if ($usuario && $this->usuarioModel->verificarSenha($senha, $usuario['senha'])) {
                    // Verifica se o usuário já tem uma sessão ativa
                    $sessaoAtiva = $this->usuarioModel->verificarSessaoAtiva($usuario['id']);

                    if ($sessaoAtiva && !$forcarLogin) {
                        // Armazena informações temporárias para a página de confirmação
                        $_SESSION['temp_login'] = [
                            'user_id' => $usuario['id'],
                            'email' => $email,
                            'senha' => $senha,
                            'empresa_id' => $empresaId,
                            'is_admin_master' => false,
                            'sessao_ativa' => $sessaoAtiva
                        ];

                        redirect('auth/confirmar_sessao');
                        return;
                    }

                    // Se não tem sessão ativa ou está forçando o login
                    if ($forcarLogin) {
                        // Limpa a sessão anterior
                        $this->usuarioModel->limparSessao($usuario['id']);
                    }

                    // Registra o login bem-sucedido
                    $this->registrarLoginSucesso($usuario, $empresaId, $empresa);
                    return;
                }
            }
        }

        // Se chegou aqui, a autenticação falhou
        error_log("Login falhou para: $email" . ($isAdminMaster ? " (admin master)" : " na empresa: $empresaId"));

        // Adiciona um pequeno atraso para dificultar ataques de força bruta
        sleep(1);

        set_flash_message('error', 'E-mail ou senha inválidos.');
        redirect('auth');
    }

    /**
     * Registra um login bem-sucedido
     */
    private function registrarLoginSucesso($usuario, $empresaId, $empresa)
    {
        // Registra a sessão do usuário
        $sessionId = session_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $this->usuarioModel->registrarSessao($usuario['id'], $sessionId, $ip, $userAgent);

        // Atualiza o último acesso
        $this->usuarioModel->update($usuario['id'], [
            'ultimo_acesso' => date('Y-m-d H:i:s')
        ]);

        // Inicia a sessão
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nome'];
        $_SESSION['empresa_id'] = $empresaId;
        $_SESSION['empresa_nome'] = $empresa['nome'];
        $_SESSION['is_admin'] = (bool) $usuario['admin'];
        $_SESSION['admin_tipo'] = $usuario['admin'] ? ($usuario['admin_tipo'] ?? 'regular') : null;
        $_SESSION['last_activity'] = time();

        // Registra o login no log
        error_log("Login bem-sucedido para: {$usuario['email']} (ID: {$usuario['id']}) na empresa: $empresaId");

        // Redireciona para o dashboard
        redirect('dashboard');
    }

    /**
     * Processa o logout
     */
    public function logout()
    {
        // Se estiver autenticado, limpa a sessão no banco de dados
        if (is_authenticated()) {
            $userId = $_SESSION['user_id'];
            $this->usuarioModel->limparSessao($userId);
        }

        // Destrói a sessão
        session_destroy();

        // Redireciona para o login
        redirect('auth');
    }

    /**
     * Exibe a página de confirmação para forçar logout de sessão ativa
     */
    public function confirmar_sessao()
    {
        // Verifica se há dados temporários na sessão
        if (!isset($_SESSION['temp_login'])) {
            redirect('auth');
            return;
        }

        $tempLogin = $_SESSION['temp_login'];

        // Busca informações do usuário
        $usuario = $this->usuarioModel->findById($tempLogin['user_id']);

        if (!$usuario) {
            unset($_SESSION['temp_login']);
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('auth');
            return;
        }

        // Renderiza a página de confirmação
        $this->render('auth/confirmar_sessao', [
            'usuario' => $usuario,
            'sessao_ativa' => $tempLogin['sessao_ativa']
        ]);
    }

    /**
     * Força o login encerrando a sessão anterior
     */
    public function forcar_login()
    {
        // Verifica se há dados temporários na sessão
        if (!isset($_SESSION['temp_login'])) {
            redirect('auth');
            return;
        }

        $tempLogin = $_SESSION['temp_login'];
        $userId = $tempLogin['user_id'];

        // Limpa a sessão anterior diretamente no banco de dados
        $sql = "UPDATE usuarios SET session_id = NULL, session_start = NULL, session_ip = NULL, session_user_agent = NULL WHERE id = :id";
        $stmt = $this->usuarioModel->getDb()->prepare($sql);
        $stmt->execute(['id' => $userId]);

        // Pequeno atraso para garantir que a sessão anterior seja encerrada
        sleep(1);

        // Redireciona para o login com parâmetros para login automático
        redirect('auth/auto_login?email=' . urlencode($tempLogin['email']) . '&token=' . $this->gerarTokenAutoLogin($tempLogin));
    }

    /**
     * Gera um token para auto login
     */
    private function gerarTokenAutoLogin($tempLogin)
    {
        $token = md5($tempLogin['email'] . $tempLogin['user_id'] . time());
        $_SESSION['auto_login'] = [
            'token' => $token,
            'email' => $tempLogin['email'],
            'senha' => $tempLogin['senha'],
            'empresa_id' => $tempLogin['empresa_id'],
            'is_admin_master' => $tempLogin['is_admin_master'],
            'expires' => time() + 60 // Expira em 1 minuto
        ];
        return $token;
    }

    /**
     * Processa o auto login após forçar logout
     */
    public function auto_login()
    {
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';

        if (empty($email) || empty($token) || !isset($_SESSION['auto_login'])) {
            redirect('auth');
            return;
        }

        $autoLogin = $_SESSION['auto_login'];

        if ($autoLogin['token'] !== $token || $autoLogin['email'] !== $email || $autoLogin['expires'] < time()) {
            unset($_SESSION['auto_login']);
            redirect('auth');
            return;
        }

        // Preenche os dados do POST para o login
        $_POST['email'] = $autoLogin['email'];
        $_POST['senha'] = $autoLogin['senha'];
        $_POST['empresa_id'] = $autoLogin['empresa_id'];
        $_POST['is_admin_master'] = $autoLogin['is_admin_master'] ? 'true' : 'false';

        // Limpa os dados de auto login
        unset($_SESSION['auto_login']);

        // Chama o método de login
        $this->login();
    }

    /**
     * Exibe o formulário de recuperação de senha
     */
    public function recuperarSenha()
    {
        // Obtém a lista de empresas ativas para o dropdown
        $empresas = $this->empresaModel->findAtivas();

        $this->render('auth/solicitar-recuperacao', [
            'empresas' => $empresas
        ]);
    }

    /**
     * Processa a solicitação de recuperação de senha
     */
    public function processarRecuperarSenha()
    {
        // Verifica se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/recuperarSenha');
            return;
        }

        // Obtém os dados do formulário
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $empresaId = isset($_POST['empresa_id']) ? intval($_POST['empresa_id']) : 0;
        $isAdminMaster = isset($_POST['is_admin_master']) && $_POST['is_admin_master'] === 'true';

        // Log para depuração
        error_log("Processando recuperação de senha para email: $email, empresa: $empresaId, admin master: " . ($isAdminMaster ? 'Sim' : 'Não'));

        // Valida os dados
        if (empty($email)) {
            error_log("Email vazio na solicitação de recuperação de senha");
            set_flash_message('error', 'O email é obrigatório.');
            redirect('auth/recuperarSenha');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Email inválido na solicitação de recuperação de senha: $email");
            set_flash_message('error', 'O email informado é inválido.');
            redirect('auth/recuperarSenha');
            return;
        }

        if (!$isAdminMaster && $empresaId <= 0) {
            error_log("Empresa não selecionada na solicitação de recuperação de senha");
            set_flash_message('error', 'A empresa é obrigatória.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Busca o usuário
        if ($isAdminMaster) {
            // Busca admin master pelo email
            $sql = "SELECT id, nome, email, ativo 
                FROM usuarios 
                WHERE email = :email 
                AND admin = 1 
                AND admin_tipo = 'master' 
                AND ativo = 1 
                LIMIT 1";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute(['email' => $email]);
        } else {
            // Busca usuário pelo email e empresa
            $sql = "SELECT id, nome, email, ativo 
                FROM usuarios 
                WHERE email = :email 
                AND empresa_id = :empresa_id 
                AND ativo = 1 
                LIMIT 1";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute([
                'email' => $email,
                'empresa_id' => $empresaId
            ]);
        }

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Adiciona um pequeno atraso para dificultar ataques de força bruta
        sleep(1);

        // Verifica se o usuário foi encontrado
        if (!$usuario) {
            error_log("Usuário não encontrado na solicitação de recuperação de senha: $email");
            // Por segurança, não informamos se o email existe ou não
            set_flash_message('success', 'Se este email estiver cadastrado, enviaremos instruções para recuperar sua senha.');
            redirect('auth');
            return;
        }

        // Gera um token de recuperação
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salva o token no banco de dados
        try {
            $sql = "UPDATE usuarios 
                SET token_recuperacao = :token, 
                    token_expiracao = :expiracao 
                WHERE id = :id";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $resultado = $stmt->execute([
                'token' => $token,
                'expiracao' => $expiracao,
                'id' => $usuario['id']
            ]);

            if (!$resultado) {
                error_log("Erro ao salvar token de recuperação para o usuário ID: {$usuario['id']}");
                set_flash_message('error', 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.');
                redirect('auth/recuperarSenha');
                return;
            }

            error_log("Token de recuperação gerado com sucesso para o usuário ID: {$usuario['id']}, token: $token, expira em: $expiracao");
        } catch (Exception $e) {
            error_log("Exceção ao salvar token de recuperação: " . $e->getMessage());
            set_flash_message('error', 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Envia o email de recuperação
        try {
            $emailService = new EmailService();
            $resultado = $emailService->enviarRecuperacaoSenha($usuario['email'], $usuario['nome'], $token);

            if ($resultado['success']) {
                error_log("Email de recuperação enviado com sucesso para: {$usuario['email']}");
                set_flash_message('success', 'Enviamos um email com instruções para recuperar sua senha. Verifique sua caixa de entrada e pasta de spam.');
            } else {
                error_log("Erro ao enviar email de recuperação para: {$usuario['email']}, erro: {$resultado['message']}");
                set_flash_message('error', 'Ocorreu um erro ao enviar o email de recuperação. Por favor, tente novamente.');
                redirect('auth/recuperarSenha');
                return;
            }
        } catch (Exception $e) {
            error_log("Exceção ao enviar email de recuperação: " . $e->getMessage());
            set_flash_message('error', 'Ocorreu um erro ao enviar o email de recuperação. Por favor, tente novamente.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Redireciona para a página de login
        redirect('auth');
    }

    /**
     * Registra uma tentativa de recuperação de senha
     */
    private function registrarTentativaRecuperacao($usuarioId, $sucesso = false)
    {
        // Aqui você pode implementar um registro de tentativas de recuperação
        // para monitorar possíveis abusos
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido';
        $status = $sucesso ? 'sucesso' : 'falha';

        error_log("Tentativa de recuperação de senha - Usuário ID: $usuarioId, IP: $ip, Status: $status");

        // Você pode criar uma tabela no banco para registrar essas tentativas
        // $this->db->insert('recuperacao_tentativas', [
        //     'usuario_id' => $usuarioId,
        //     'ip' => $ip,
        //     'user_agent' => $userAgent,
        //     'sucesso' => $sucesso,
        //     'data_hora' => date('Y-m-d H:i:s')
        // ]);
    }

    /**
     * Exibe o formulário de redefinição de senha
     */
    public function redefinirSenha()
    {
        // Se o usuário já estiver logado, redireciona para o dashboard
        if (is_authenticated()) {
            redirect('dashboard');
            return;
        }

        // Obtém o token da URL
        $token = isset($_GET['token']) ? trim($_GET['token']) : '';

        // Log para depuração
        error_log("Tentativa de redefinição de senha com token: $token");

        if (empty($token)) {
            error_log("Token vazio na solicitação de redefinição de senha");
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        // Busca o usuário pelo token
        $sql = "SELECT id, nome, email, token_recuperacao, token_expiracao, ativo 
            FROM usuarios 
            WHERE token_recuperacao = :token 
            LIMIT 1";

        $stmt = $this->usuarioModel->getDb()->prepare($sql);
        $stmt->execute(['token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Log do resultado da busca
        if ($usuario) {
            error_log("Usuário encontrado para o token: ID={$usuario['id']}, Nome={$usuario['nome']}, Token={$usuario['token_recuperacao']}");
        } else {
            error_log("Nenhum usuário encontrado com o token: $token");
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        // Verifica se o token expirou
        $agora = new DateTime();
        $expiracao = new DateTime($usuario['token_expiracao']);

        if ($agora > $expiracao) {
            error_log("Token expirado. Expiração: {$usuario['token_expiracao']}, Agora: " . $agora->format('Y-m-d H:i:s'));
            set_flash_message('error', 'O link de recuperação de senha expirou. Por favor, solicite um novo link.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Verifica se o usuário está ativo
        if (!$usuario['ativo']) {
            error_log("Usuário inativo tentando redefinir senha: ID={$usuario['id']}");
            set_flash_message('error', 'Esta conta está desativada. Entre em contato com o administrador.');
            redirect('auth');
            return;
        }

        // Renderiza a página de redefinição de senha
        $this->render('auth/redefinir-senha', [
            'token' => $token
        ]);
    }

    /**
     * Processa a redefinição de senha
     */
    public function processarRedefinirSenha()
    {
        // Verifica se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth');
            return;
        }

        // Obtém os dados do formulário
        $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
        $confirmarSenha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

        // Log para depuração
        error_log("Processando redefinição de senha com token: $token");

        // Valida os dados
        if (empty($token)) {
            error_log("Token vazio no processamento de redefinição de senha");
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        if (empty($senha)) {
            error_log("Senha vazia no processamento de redefinição de senha");
            set_flash_message('error', 'A senha é obrigatória.');
            redirect("auth/redefinirSenha?token=$token");
            return;
        }

        if (strlen($senha) < 8) {
            error_log("Senha muito curta no processamento de redefinição de senha");
            set_flash_message('error', 'A senha deve ter pelo menos 8 caracteres.');
            redirect("auth/redefinirSenha?token=$token");
            return;
        }

        if ($senha !== $confirmarSenha) {
            error_log("Senhas não coincidem no processamento de redefinição de senha");
            set_flash_message('error', 'As senhas não coincidem.');
            redirect("auth/redefinirSenha?token=$token");
            return;
        }

        // Busca o usuário pelo token
        $sql = "SELECT id, nome, email, token_recuperacao, token_expiracao, ativo 
            FROM usuarios 
            WHERE token_recuperacao = :token 
            LIMIT 1";

        $stmt = $this->usuarioModel->getDb()->prepare($sql);
        $stmt->execute(['token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário foi encontrado
        if (!$usuario) {
            error_log("Nenhum usuário encontrado com o token: $token");
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        // Verifica se o token expirou
        $agora = new DateTime();
        $expiracao = new DateTime($usuario['token_expiracao']);

        if ($agora > $expiracao) {
            error_log("Token expirado. Expiração: {$usuario['token_expiracao']}, Agora: " . $agora->format('Y-m-d H:i:s'));
            set_flash_message('error', 'O link de recuperação de senha expirou. Por favor, solicite um novo link.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Verifica se o usuário está ativo
        if (!$usuario['ativo']) {
            error_log("Usuário inativo tentando redefinir senha: ID={$usuario['id']}");
            set_flash_message('error', 'Esta conta está desativada. Entre em contato com o administrador.');
            redirect('auth');
            return;
        }

        // Gera o hash da nova senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Atualiza a senha e limpa o token
        try {
            $sql = "UPDATE usuarios 
                SET senha = :senha, 
                    token_recuperacao = NULL, 
                    token_expiracao = NULL, 
                    data_atualizacao = NOW() 
                WHERE id = :id";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $resultado = $stmt->execute([
                'senha' => $senhaHash,
                'id' => $usuario['id']
            ]);

            if ($resultado) {
                error_log("Senha redefinida com sucesso para o usuário ID: {$usuario['id']}");
                set_flash_message('success', 'Sua senha foi redefinida com sucesso. Você já pode fazer login com sua nova senha.');
                redirect('auth');
            } else {
                error_log("Erro ao atualizar senha no banco de dados para o usuário ID: {$usuario['id']}");
                set_flash_message('error', 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente.');
                redirect("auth/redefinirSenha?token=$token");
            }
        } catch (Exception $e) {
            error_log("Exceção ao atualizar senha: " . $e->getMessage());
            set_flash_message('error', 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente.');
            redirect("auth/redefinirSenha?token=$token");
        }
    }

    /**
     * Busca todas as empresas associadas a um email (para AJAX)
     */
    public function buscarEmpresasDoUsuario()
    {
        // Obtém o email da requisição
        $email = sanitize_input($_GET['email'] ?? '');

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
            exit;
        }

        // Log para depuração
        error_log("Buscando empresas para o email: $email");

        // Verifica primeiro se é um admin master
        $adminMaster = $this->usuarioModel->findOne(
            'email = :email AND ativo = 1 AND admin = 1 AND admin_tipo = "master"',
            ['email' => $email]
        );

        if ($adminMaster) {
            // Se for admin master, retorna essa informação
            echo json_encode([
                'success' => true,
                'isAdminMaster' => true,
                'empresa_id' => $adminMaster['empresa_id'],
                'empresas' => [] // Não precisa de lista de empresas
            ]);
            exit;
        }

        // Busca todas as empresas onde o usuário está cadastrado
        $sql = "SELECT u.empresa_id, e.nome as empresa_nome, u.admin, u.admin_tipo 
            FROM usuarios u 
            JOIN empresas e ON u.empresa_id = e.id 
            WHERE u.email = :email AND u.ativo = 1 AND e.ativo = 1 
            ORDER BY e.nome ASC";

        $stmt = $this->usuarioModel->getDb()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $empresasDoUsuario = $stmt->fetchAll();

        if (count($empresasDoUsuario) > 0) {
            // Formata a resposta
            $empresas = [];
            $isAdminRegular = false;

            foreach ($empresasDoUsuario as $empresa) {
                $empresas[] = [
                    'id' => $empresa['empresa_id'],
                    'nome' => $empresa['empresa_nome']
                ];

                // Verifica se é admin regular em pelo menos uma empresa
                if ($empresa['admin'] == 1 && $empresa['admin_tipo'] == 'regular') {
                    $isAdminRegular = true;
                }
            }

            echo json_encode([
                'success' => true,
                'isAdminMaster' => false,
                'isAdminRegular' => $isAdminRegular,
                'empresas' => $empresas,
                'totalEmpresas' => count($empresas)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuário não encontrado em nenhuma empresa ativa'
            ]);
        }

        exit;
    }

    /**
     * Verifica se um email pertence a um admin master (para AJAX)
     */
    public function verificarAdminMaster()
    {
        // Obtém o email da requisição
        $email = sanitize_input($_GET['email'] ?? '');

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
            exit;
        }

        // Log para depuração
        error_log("Verificando se o email é admin master: $email");

        // Busca o usuário pelo email
        $usuario = $this->usuarioModel->findOne('email = :email AND ativo = 1', ['email' => $email]);

        if ($usuario && $usuario['admin'] == 1 && $usuario['admin_tipo'] == 'master') {
            error_log("Usuário é admin master: ID={$usuario['id']}");

            echo json_encode([
                'success' => true,
                'isAdminMaster' => true,
                'empresa_id' => $usuario['empresa_id'] // Enviamos a empresa_id mesmo assim para referência
            ]);
        } else {
            error_log("Usuário não é admin master ou não foi encontrado: $email");
            echo json_encode([
                'success' => true,
                'isAdminMaster' => false
            ]);
        }

        exit;
    }

    /**
     * Envia um e-mail de teste para verificar a configuração do PHPMailer
     * Útil para depuração das configurações de e-mail
     */
    public function testarEmail()
    {
        // Verifica se o usuário é admin
        if (!is_authenticated() || !$_SESSION['is_admin']) {
            redirect('auth');
            return;
        }

        $para = sanitize_input($_GET['email'] ?? $_SESSION['user_email'] ?? '');

        if (empty($para) || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
            exit;
        }

        $assunto = 'Teste de Configuração de E-mail - Sistema de Gestão de Chamados';
        $mensagem = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
            <h2 style='color: #2563eb; margin-bottom: 20px;'>Teste de E-mail</h2>
            
            <p>Este é um e-mail de teste para verificar a configuração do sistema de envio de e-mails.</p>
            
            <p>Se você está recebendo este e-mail, significa que a configuração está funcionando corretamente!</p>
            
            <p>Detalhes técnicos:</p>
            <ul>
                <li>Data e hora: " . date('d/m/Y H:i:s') . "</li>
                <li>Servidor: " . $_SERVER['SERVER_NAME'] . "</li>
                <li>PHPMailer: Ativo</li>
            </ul>
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>
                <p>Atenciosamente,<br>Sistema de Gestão de Chamados</p>
                <p>Este é um e-mail automático, por favor não responda.</p>
            </div>
        </div>
        ";

        $resultado = $this->emailService->enviar($para, $assunto, $mensagem);

        echo json_encode($resultado);
        exit;
    }

    /**
     * Verifica se a sessão atual é válida (para AJAX)
     */
    public function verificar_sessao()
    {
        header('Content-Type: application/json');

        if (!is_authenticated()) {
            echo json_encode(['valid' => false]);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $sessionId = session_id();

        $isValid = $this->usuarioModel->validarSessao($userId, $sessionId);

        echo json_encode(['valid' => $isValid]);
        exit;
    }
}
