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
     * Processa a recuperação de senha
     */
    public function processarRecuperarSenha()
    {
        // Verifica se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/recuperarSenha');
            return;
        }

        $email = sanitize_input($_POST['email'] ?? '');
        $empresaId = intval($_POST['empresa_id'] ?? 0);
        $isAdminMaster = isset($_POST['is_admin_master']) && $_POST['is_admin_master'] === 'true';

        // Valida os campos
        $errors = [];

        if (empty($email)) {
            $errors[] = 'O campo E-mail é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O formato do e-mail é inválido.';
        }

        // Só valida empresa_id se não for admin master
        if (!$isAdminMaster && $empresaId <= 0) {
            $errors[] = 'O campo Empresa é obrigatório.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('auth/recuperarSenha');
            return;
        }

        // Se for admin master, busca o usuário pelo email apenas
        if ($isAdminMaster) {
            $usuario = $this->usuarioModel->findOne('email = :email AND admin = 1 AND admin_tipo = "master" AND ativo = 1', [
                'email' => $email
            ]);

            // Se encontrou, usa a empresa do admin master
            if ($usuario) {
                $empresaId = $usuario['empresa_id'];
            }
        } else {
            // Caso contrário, busca o usuário pelo email e empresa
            $usuario = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND ativo = 1', [
                'email' => $email,
                'empresa_id' => $empresaId
            ]);
        }

        // Adiciona um pequeno atraso para dificultar ataques de enumeração
        sleep(1);

        if ($usuario) {
            // Gera um token de recuperação
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Salva o token no banco
            $this->usuarioModel->update($usuario['id'], [
                'token_recuperacao' => $token,
                'token_expiracao' => $expira
            ]);

            // Envia o e-mail de recuperação usando o EmailService
            $resultado = $this->emailService->enviarRecuperacaoSenha($usuario['email'], $usuario['nome'], $token);

            // Registra o resultado
            if ($resultado['success']) {
                error_log("E-mail de recuperação enviado com sucesso para {$usuario['email']}");
            } else {
                error_log("Falha ao enviar e-mail de recuperação para {$usuario['email']}: " . $resultado['message']);
            }

            // Registra a tentativa de recuperação
            $this->registrarTentativaRecuperacao($usuario['id'], $resultado['success']);

            set_flash_message('success', 'Enviamos um e-mail com instruções para recuperar sua senha. Verifique sua caixa de entrada e spam.');
        } else {
            // Não informamos se o e-mail existe ou não por segurança
            // Mas registramos a tentativa para monitoramento
            error_log("Tentativa de recuperação para e-mail não encontrado: $email na empresa: $empresaId");

            set_flash_message('success', 'Se este e-mail estiver cadastrado na empresa selecionada, enviaremos instruções para recuperar sua senha.');
        }

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
        $token = sanitize_input($_GET['token'] ?? '');

        if (empty($token)) {
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        // Busca o usuário pelo token
        $usuario = $this->usuarioModel->findOne('token_recuperacao = :token AND token_expiracao > NOW() AND ativo = 1', [
            'token' => $token
        ]);

        if (!$usuario) {
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

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

        $token = sanitize_input($_POST['token'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Valida os campos
        $errors = [];

        if (empty($token)) {
            $errors[] = 'Token de recuperação inválido.';
        }

        if (empty($senha)) {
            $errors[] = 'O campo Senha é obrigatório.';
        } elseif (strlen($senha) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }

        if ($senha !== $confirmarSenha) {
            $errors[] = 'As senhas não coincidem.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect("auth/redefinirSenha?token={$token}");
            return;
        }

        // Busca o usuário pelo token
        $usuario = $this->usuarioModel->findOne('token_recuperacao = :token AND token_expiracao > NOW() AND ativo = 1', [
            'token' => $token
        ]);

        if (!$usuario) {
            set_flash_message('error', 'Token de recuperação inválido ou expirado.');
            redirect('auth');
            return;
        }

        // Gera o hash da nova senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Atualiza a senha e limpa o token
        $this->usuarioModel->update($usuario['id'], [
            'senha' => $senhaHash,
            'token_recuperacao' => null,
            'token_expiracao' => null,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ]);

        // Registra a alteração de senha
        error_log("Senha redefinida com sucesso para o usuário ID: {$usuario['id']}");

        set_flash_message('success', 'Sua senha foi redefinida com sucesso. Você já pode fazer login com sua nova senha.');
        redirect('auth');
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
