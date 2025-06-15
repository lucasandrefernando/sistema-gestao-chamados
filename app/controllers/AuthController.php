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
        $email = sanitize_input($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $empresaId = intval($_POST['empresa_id'] ?? 0);

        // Log para depuração
        error_log("Tentativa de login - Email: $email, Senha: " . substr($senha, 0, 3) . "*** Empresa ID: $empresaId");

        // Valida os campos
        $errors = $this->validateRequired(
            ['email' => $email, 'senha' => $senha, 'empresa_id' => $empresaId],
            ['email' => 'E-mail', 'senha' => 'Senha', 'empresa_id' => 'Empresa']
        );

        if (!empty($errors)) {
            error_log("Erro de validação: " . implode(', ', $errors));
            set_flash_message('error', implode('<br>', $errors));
            redirect('auth');
            return;
        }

        // Verifica se a empresa existe e está ativa
        $empresa = $this->empresaModel->findById($empresaId);
        if (!$empresa || !$empresa['ativo']) {
            error_log("Empresa inválida ou inativa: $empresaId");
            set_flash_message('error', 'Empresa inválida ou inativa.');
            redirect('auth');
            return;
        }

        // Primeiro, verifica se o usuário existe em qualquer empresa
        $usuarioGlobal = $this->usuarioModel->findOne('email = :email AND ativo = 1', ['email' => $email]);

        // Se não existe, já retorna erro
        if (!$usuarioGlobal) {
            error_log("Usuário não encontrado: $email");
            set_flash_message('error', 'E-mail ou senha inválidos.');
            redirect('auth');
            return;
        }

        // Verifica se é um admin master
        $isAdminMaster = $usuarioGlobal['admin'] == 1 && $usuarioGlobal['admin_tipo'] == 'master';

        // Se for admin master, pode logar em qualquer empresa
        if ($isAdminMaster) {
            // Verifica a senha
            if ($this->usuarioModel->verificarSenha($senha, $usuarioGlobal['senha'])) {
                // Atualiza último acesso
                $this->usuarioModel->update($usuarioGlobal['id'], [
                    'ultimo_acesso' => date('Y-m-d H:i:s')
                ]);

                // Inicia a sessão
                $_SESSION['user_id'] = $usuarioGlobal['id'];
                $_SESSION['user_name'] = $usuarioGlobal['nome'];
                $_SESSION['empresa_id'] = $empresaId; // Usa a empresa selecionada
                $_SESSION['empresa_nome'] = $empresa['nome'];
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_tipo'] = 'master';

                error_log("Login bem-sucedido para admin master: $email na empresa: $empresaId");
                redirect('dashboard');
                return;
            }
        } else {
            // Se não for admin master, só pode logar na própria empresa
            $usuario = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND ativo = 1', [
                'email' => $email,
                'empresa_id' => $empresaId
            ]);

            if ($usuario && $this->usuarioModel->verificarSenha($senha, $usuario['senha'])) {
                // Atualiza último acesso
                $this->usuarioModel->update($usuario['id'], [
                    'ultimo_acesso' => date('Y-m-d H:i:s')
                ]);

                // Inicia a sessão
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_name'] = $usuario['nome'];
                $_SESSION['empresa_id'] = $usuario['empresa_id'];
                $_SESSION['empresa_nome'] = $empresa['nome'];
                $_SESSION['is_admin'] = (bool) $usuario['admin'];
                $_SESSION['admin_tipo'] = $usuario['admin'] ? ($usuario['admin_tipo'] ?? 'regular') : null;

                error_log("Login bem-sucedido para: $email na empresa: $empresaId");
                redirect('dashboard');
                return;
            }
        }

        // Se chegou aqui, a autenticação falhou
        error_log("Login falhou para: $email na empresa: $empresaId");
        set_flash_message('error', 'E-mail ou senha inválidos para a empresa selecionada.');
        redirect('auth');
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
        $email = sanitize_input($_POST['email'] ?? '');
        $empresaId = intval($_POST['empresa_id'] ?? 0);

        // Valida os campos
        if (empty($email) || $empresaId <= 0) {
            set_flash_message('error', 'Os campos E-mail e Empresa são obrigatórios.');
            redirect('auth/recuperarSenha');
            return;
        }

        // Verifica se o usuário existe na empresa especificada
        $usuario = $this->usuarioModel->findOne('email = :email AND empresa_id = :empresa_id AND ativo = 1', [
            'email' => $email,
            'empresa_id' => $empresaId
        ]);

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
            set_flash_message('success', 'Se este e-mail estiver cadastrado na empresa selecionada, enviaremos instruções para recuperar sua senha.');
        }

        redirect('auth');
    }
}
