<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Usuario.php';
require_once ROOT_DIR . '/app/models/EmailService.php';

class PerfilController extends Controller
{
    private $usuarioModel;
    private $emailService;

    public function __construct()
    {
        if (!is_authenticated()) {
            redirect('auth');
            exit;
        }

        $this->usuarioModel = new Usuario();
        $this->emailService = new EmailService();
    }

    /**
     * Página principal do perfil
     */
    public function index()
    {
        try {
            $usuarioId = get_user_id();
            $usuario = $this->usuarioModel->findById($usuarioId);

            if (!$usuario) {
                set_flash_message('error', 'Usuário não encontrado.');
                redirect('dashboard');
                exit;
            }

            // Buscar empresa
            try {
                $sql = "SELECT nome FROM empresas WHERE id = :empresa_id";
                $stmt = $this->usuarioModel->getDb()->prepare($sql);
                $stmt->execute(['empresa_id' => $usuario['empresa_id']]);
                $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
                $usuario['empresa_nome'] = $empresa ? $empresa['nome'] : 'Não definida';
            } catch (Exception $e) {
                error_log('Erro ao buscar empresa: ' . $e->getMessage());
                $usuario['empresa_nome'] = 'Não definida';
            }

            // Buscar setor principal
            try {
                $sql = "SELECT s.nome FROM setores s 
                        JOIN usuarios_setores us ON s.id = us.setor_id 
                        WHERE us.usuario_id = :usuario_id AND us.principal = 1 
                        LIMIT 1";
                $stmt = $this->usuarioModel->getDb()->prepare($sql);
                $stmt->execute(['usuario_id' => $usuarioId]);
                $setor = $stmt->fetch(PDO::FETCH_ASSOC);
                $usuario['setor_nome'] = $setor ? $setor['nome'] : 'Não definido';
            } catch (Exception $e) {
                error_log('Erro ao buscar setor: ' . $e->getMessage());
                $usuario['setor_nome'] = 'Não definido';
            }

            $this->render('perfil/index', ['usuario' => $usuario]);
        } catch (Exception $e) {
            error_log('Erro no método index do PerfilController: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao carregar perfil.');
            redirect('dashboard');
        }
    }

    /**
     * Atualizar dados pessoais
     */
    public function atualizarDados()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Método inválido.');
            redirect('perfil');
            exit;
        }

        try {
            $usuarioId = get_user_id();
            $nome = trim($_POST['nome'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');

            // Validações
            if (empty($nome)) {
                set_flash_message('error', 'O nome é obrigatório.');
                redirect('perfil');
                exit;
            }

            if (strlen($nome) < 2) {
                set_flash_message('error', 'O nome deve ter pelo menos 2 caracteres.');
                redirect('perfil');
                exit;
            }

            // Atualizar no banco
            $sql = "UPDATE usuarios SET nome = :nome, cargo = :cargo, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $result = $stmt->execute([
                'nome' => $nome,
                'cargo' => $cargo ?: null,
                'id' => $usuarioId
            ]);

            if ($result && $stmt->rowCount() > 0) {
                // Atualizar sessão
                $_SESSION['user_name'] = $nome;

                // Log da ação
                $this->registrarLog($usuarioId, 'dados_atualizados', 'Dados pessoais atualizados');

                set_flash_message('success', 'Dados atualizados com sucesso!');
            } else {
                set_flash_message('error', 'Nenhuma alteração foi feita ou erro ao atualizar.');
            }
        } catch (Exception $e) {
            error_log('Erro ao atualizar dados: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao atualizar dados: ' . $e->getMessage());
        }

        redirect('perfil');
    }

    /**
     * Alterar senha (seguindo o padrão do AuthController)
     */
    public function alterarSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Método inválido.');
            redirect('perfil');
            exit;
        }

        try {
            $usuarioId = get_user_id();
            $senhaAtual = $_POST['senha_atual'] ?? '';
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            // Validações
            if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
                set_flash_message('error', 'Todos os campos são obrigatórios.');
                redirect('perfil');
                exit;
            }

            if (strlen($novaSenha) < 8) {
                set_flash_message('error', 'A nova senha deve ter pelo menos 8 caracteres.');
                redirect('perfil');
                exit;
            }

            if ($novaSenha !== $confirmarSenha) {
                set_flash_message('error', 'As senhas não coincidem.');
                redirect('perfil');
                exit;
            }

            // Buscar usuário atual
            $sql = "SELECT id, nome, email, senha FROM usuarios WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute(['id' => $usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                set_flash_message('error', 'Usuário não encontrado.');
                redirect('perfil');
                exit;
            }

            // Verificar senha atual usando o método do modelo
            if (!$this->usuarioModel->verificarSenha($senhaAtual, $usuario['senha'])) {
                set_flash_message('error', 'Senha atual incorreta.');
                redirect('perfil');
                exit;
            }

            // Atualizar senha
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha = :senha, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $result = $stmt->execute([
                'senha' => $novaSenhaHash,
                'id' => $usuarioId
            ]);

            if ($result && $stmt->rowCount() > 0) {
                // Log da ação
                $this->registrarLog($usuarioId, 'senha_alterada', 'Senha alterada pelo usuário');

                // Enviar email de notificação
                $this->enviarEmailAlteracaoSenha($usuario['email'], $usuario['nome']);

                // Limpar sessão atual (seguindo o padrão do AuthController)
                $this->usuarioModel->limparSessao($usuarioId);

                set_flash_message('success', 'Senha alterada com sucesso! Você será desconectado por segurança.');

                // Destruir sessão para forçar novo login
                session_destroy();
                redirect('auth');
            } else {
                set_flash_message('error', 'Erro ao alterar senha.');
                redirect('perfil');
            }
        } catch (Exception $e) {
            error_log('Erro ao alterar senha: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao alterar senha: ' . $e->getMessage());
            redirect('perfil');
        }
    }

    /**
     * Desativar conta
     */
    public function desativar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Método inválido.');
            redirect('perfil');
            exit;
        }

        try {
            $usuarioId = get_user_id();
            $senha = $_POST['senha'] ?? '';

            if (empty($senha)) {
                set_flash_message('error', 'A senha é obrigatória.');
                redirect('perfil');
                exit;
            }

            // Verificar senha
            $sql = "SELECT id, nome, email, senha, admin, admin_tipo FROM usuarios WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute(['id' => $usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                set_flash_message('error', 'Usuário não encontrado.');
                redirect('perfil');
                exit;
            }

            if (!$this->usuarioModel->verificarSenha($senha, $usuario['senha'])) {
                set_flash_message('error', 'Senha incorreta.');
                redirect('perfil');
                exit;
            }

            // Verificar se é admin master (não pode se desativar)
            if ($usuario['admin'] && $usuario['admin_tipo'] === 'master') {
                set_flash_message('error', 'Administradores master não podem desativar suas próprias contas.');
                redirect('perfil');
                exit;
            }

            // Desativar usuário
            $sql = "UPDATE usuarios SET ativo = 0, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $result = $stmt->execute(['id' => $usuarioId]);

            if ($result && $stmt->rowCount() > 0) {
                // Log da ação
                $this->registrarLog($usuarioId, 'conta_desativada', 'Conta desativada pelo próprio usuário');

                // Limpar sessão (seguindo o padrão do AuthController)
                $this->usuarioModel->limparSessao($usuarioId);

                set_flash_message('success', 'Conta desativada com sucesso. Entre em contato com o administrador para reativação.');

                // Destruir sessão
                session_destroy();
                redirect('auth');
            } else {
                set_flash_message('error', 'Erro ao desativar conta.');
                redirect('perfil');
            }
        } catch (Exception $e) {
            error_log('Erro ao desativar conta: ' . $e->getMessage());
            set_flash_message('error', 'Erro ao desativar conta: ' . $e->getMessage());
            redirect('perfil');
        }
    }

    /**
     * Enviar email de notificação sobre alteração de senha
     */
    private function enviarEmailAlteracaoSenha($email, $nome)
    {
        try {
            $assunto = 'Senha Alterada - Sistema de Gestão de Chamados';
            $dataHora = date('d/m/Y H:i:s');
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';

            $mensagem = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                <div style='background: linear-gradient(135deg, #667eea, #764ba2); padding: 20px; border-radius: 8px 8px 0 0; color: white; text-align: center;'>
                    <h2 style='margin: 0; color: white;'>🔐 Senha Alterada</h2>
                </div>
                
                <div style='padding: 30px 20px;'>
                    <p>Olá, <strong>{$nome}</strong>!</p>
                    
                    <p>Sua senha foi alterada com sucesso no Sistema de Gestão de Chamados.</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #495057;'>Detalhes da Alteração:</h3>
                        <ul style='margin: 0; padding-left: 20px;'>
                            <li><strong>Data e Hora:</strong> {$dataHora}</li>
                            <li><strong>IP:</strong> {$ip}</li>
                            <li><strong>Ação:</strong> Alteração de senha pelo usuário</li>
                        </ul>
                    </div>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 0; color: #856404;'>
                            <strong>⚠️ Importante:</strong> Se você não fez esta alteração, entre em contato imediatamente com o administrador do sistema.
                        </p>
                    </div>
                    
                    <p>Por segurança, você foi desconectado de todas as sessões ativas e precisará fazer login novamente.</p>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; text-align: center; color: #6c757d; font-size: 14px;'>
                    <p style='margin: 0;'>Sistema de Gestão de Chamados</p>
                    <p style='margin: 5px 0 0 0;'>Este é um e-mail automático, não responda.</p>
                </div>
            </div>
            ";

            $resultado = $this->emailService->enviar($email, $assunto, $mensagem);

            if ($resultado['success']) {
                error_log("Email de alteração de senha enviado com sucesso para: {$email}");
            } else {
                error_log("Erro ao enviar email de alteração de senha para: {$email}, erro: {$resultado['message']}");
            }
        } catch (Exception $e) {
            error_log('Erro ao enviar email de alteração de senha: ' . $e->getMessage());
        }
    }

    /**
     * Registrar log de atividades
     */
    private function registrarLog($usuarioId, $acao, $descricao)
    {
        try {
            $sql = "INSERT INTO logs (usuario_id, acao, descricao, ip, data_criacao) 
                    VALUES (:usuario_id, :acao, :descricao, :ip, NOW())";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuarioId,
                'acao' => $acao,
                'descricao' => $descricao,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log('Erro ao registrar log: ' . $e->getMessage());
        }
    }
}
