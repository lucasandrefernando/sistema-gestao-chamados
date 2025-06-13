<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';

/**
 * Controlador para autenticação
 */
class AuthController extends Controller
{
    private $usuarioModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
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

        $this->render('auth/login');
    }

    /**
     * Processa o login
     */
    public function login()
    {
        $email = sanitize_input($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Log para depuração
        error_log("Tentativa de login - Email: $email, Senha: " . substr($senha, 0, 3) . "***");

        // Valida os campos
        $errors = $this->validateRequired(
            ['email' => $email, 'senha' => $senha],
            ['email' => 'E-mail', 'senha' => 'Senha']
        );

        if (!empty($errors)) {
            error_log("Erro de validação: " . implode(', ', $errors));
            set_flash_message('error', implode('<br>', $errors));
            redirect('auth');
            return;
        }

        // Tenta autenticar
        $usuario = $this->usuarioModel->autenticar($email, $senha);

        if ($usuario) {
            error_log("Login bem-sucedido para: $email");

            // Inicia a sessão
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'];
            $_SESSION['is_admin'] = (bool) $usuario['admin'];

            // Redireciona para o dashboard
            redirect('dashboard');
        } else {
            error_log("Login falhou para: $email");
            set_flash_message('error', 'E-mail ou senha inválidos.');
            redirect('auth');
        }
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
        $this->render('auth/recuperar-senha');
    }

    /**
     * Processa a recuperação de senha
     */
    public function processarRecuperarSenha()
    {
        $email = sanitize_input($_POST['email'] ?? '');

        // Valida o campo
        if (empty($email)) {
            set_flash_message('error', 'O campo E-mail é obrigatório.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Verifica se o usuário existe
        $usuario = $this->usuarioModel->findOne('email = :email AND ativo = 1', ['email' => $email]);

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

            set_flash_message('success', 'Enviamos um e-mail com instruções para recuperar sua senha.');
        } else {
            // Não informamos se o e-mail existe ou não por segurança
            set_flash_message('success', 'Se este e-mail estiver cadastrado, enviaremos instruções para recuperar sua senha.');
        }

        redirect('auth');
    }
}
