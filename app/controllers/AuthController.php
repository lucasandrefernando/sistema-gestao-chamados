<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/Empresa.php';

/**
 * Controlador para autenticação
 */
class AuthController extends Controller
{
    private $usuarioModel;
    private $empresaModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
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
        // Atualiza apenas o último acesso
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
        // Destrói a sessão
        session_destroy();

        // Redireciona para o login
        redirect('auth');
    }

    /**
     * Exibe o formulário de recuperação de senha
     */
    public function recuperarSenha()
    {
        // Obtém a lista de empresas ativas para o dropdown
        $empresas = $this->empresaModel->findAtivas();

        $this->render('auth/recuperar-senha', [
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

        // Valida os campos
        $errors = [];

        if (empty($email)) {
            $errors[] = 'O campo E-mail é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O formato do e-mail é inválido.';
        }

        if ($empresaId <= 0) {
            $errors[] = 'O campo Empresa é obrigatório.';
        }

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('auth/recuperarSenha');
            return;
        }

        // Verifica se o usuário existe na empresa especificada
        $usuario = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND ativo = 1', [
            'email' => $email,
            'empresa_id' => $empresaId
        ]);

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

            // Envia o e-mail (implementação simplificada)
            $resetUrl = base_url("auth/redefinirSenha?token={$token}");
            $mensagem = "Olá {$usuario['nome']},\n\n";
            $mensagem .= "Você solicitou a recuperação de senha. Clique no link abaixo para redefinir sua senha:\n\n";
            $mensagem .= $resetUrl . "\n\n";
            $mensagem .= "Este link expira em 1 hora.\n\n";
            $mensagem .= "Se você não solicitou esta recuperação, ignore este e-mail.\n\n";
            $mensagem .= "Atenciosamente,\nEquipe Eagle Telecom";

            // Aqui você deve implementar o envio de e-mail real
            // mail($usuario['email'], 'Recuperação de Senha', $mensagem);

            // Para fins de desenvolvimento, apenas exibe a mensagem
            error_log("E-mail de recuperação para {$usuario['email']}: {$resetUrl}");

            // Registra a tentativa de recuperação
            $this->registrarTentativaRecuperacao($usuario['id'], true);

            set_flash_message('success', 'Enviamos um e-mail com instruções para recuperar sua senha.');
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
}
