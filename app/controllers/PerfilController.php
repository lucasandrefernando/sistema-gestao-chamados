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
     * Página principal do perfil - CORREÇÃO DEFINITIVA PARA PRIMEIRO ACESSO
     */
    public function index()
    {
        try {
            // ✅ CORREÇÃO: Garantir que sessão esteja ativa
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $usuarioId = get_user_id();

            if (!$usuarioId) {
                set_flash_message('error', 'Usuário não autenticado.');
                redirect('auth');
                exit;
            }

            // ✅ CORREÇÃO: Verificar se empresa_id está na sessão
            if (!isset($_SESSION['empresa_id']) || empty($_SESSION['empresa_id'])) {
                error_log('Empresa ID não encontrada na sessão, buscando do banco...');

                // Buscar empresa_id do banco e atualizar sessão
                $sqlEmpresaId = "SELECT empresa_id FROM usuarios WHERE id = :id";
                $stmtEmpresaId = $this->usuarioModel->getDb()->prepare($sqlEmpresaId);
                $stmtEmpresaId->execute(['id' => $usuarioId]);
                $userEmpresa = $stmtEmpresaId->fetch(PDO::FETCH_ASSOC);

                if ($userEmpresa && !empty($userEmpresa['empresa_id'])) {
                    $_SESSION['empresa_id'] = $userEmpresa['empresa_id'];
                    error_log('Empresa ID atualizada na sessão: ' . $userEmpresa['empresa_id']);
                }
            }

            // ✅ CORREÇÃO: Buscar TODOS os dados em uma query única
            $sql = "SELECT 
                    u.id,
                    u.nome,
                    u.email,
                    u.cargo,
                    u.admin,
                    u.admin_tipo,
                    u.ativo,
                    u.ultimo_acesso,
                    u.empresa_id,
                    u.data_criacao,
                    e.nome as empresa_nome
                FROM usuarios u 
                LEFT JOIN empresas e ON u.empresa_id = e.id 
                WHERE u.id = :id AND u.ativo = 1";

            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $stmt->execute(['id' => $usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                set_flash_message('error', 'Usuário não encontrado.');
                redirect('dashboard');
                exit;
            }

            // ✅ CORREÇÃO: Se empresa_nome vier NULL, buscar separadamente
            if (empty($usuario['empresa_nome']) && !empty($usuario['empresa_id'])) {
                error_log('Empresa nome vazio, buscando separadamente...');

                $sqlEmpresa = "SELECT nome FROM empresas WHERE id = :empresa_id";
                $stmtEmpresa = $this->usuarioModel->getDb()->prepare($sqlEmpresa);
                $stmtEmpresa->execute(['empresa_id' => $usuario['empresa_id']]);
                $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

                if ($empresa && !empty($empresa['nome'])) {
                    $usuario['empresa_nome'] = $empresa['nome'];
                    error_log('Empresa encontrada: ' . $empresa['nome']);
                } else {
                    $usuario['empresa_nome'] = 'Empresa não encontrada';
                    error_log('Empresa não encontrada para ID: ' . $usuario['empresa_id']);
                }
            } else if (empty($usuario['empresa_nome'])) {
                $usuario['empresa_nome'] = 'Empresa não definida';
            }

            // ✅ CORREÇÃO: Buscar setores separadamente
            $sqlSetores = "SELECT 
                        s.id, 
                        s.nome, 
                        us.principal
                    FROM usuarios_setores us
                    INNER JOIN setores s ON us.setor_id = s.id 
                    WHERE us.usuario_id = :usuario_id 
                    AND s.ativo = 1 
                    AND s.removido = 0
                    ORDER BY us.principal DESC, s.nome ASC";

            $stmtSetores = $this->usuarioModel->getDb()->prepare($sqlSetores);
            $stmtSetores->execute(['usuario_id' => $usuarioId]);
            $setoresDetalhados = $stmtSetores->fetchAll(PDO::FETCH_ASSOC);

            error_log('Setores encontrados: ' . count($setoresDetalhados));

            // ✅ SEMPRE DEFINIR TODAS AS VARIÁVEIS
            $usuario['setores_detalhados'] = $setoresDetalhados;
            $usuario['setores_nomes'] = array_column($setoresDetalhados, 'nome');
            $usuario['total_setores'] = count($setoresDetalhados);

            // Criar resumo
            if (count($setoresDetalhados) > 0) {
                if (count($setoresDetalhados) == 1) {
                    $usuario['setores_resumo'] = $setoresDetalhados[0]['nome'];
                } else if (count($setoresDetalhados) == 2) {
                    $usuario['setores_resumo'] = $setoresDetalhados[0]['nome'] . ' e ' . $setoresDetalhados[1]['nome'];
                } else {
                    $usuario['setores_resumo'] = $setoresDetalhados[0]['nome'] . ' e mais ' . (count($setoresDetalhados) - 1) . ' outros';
                }
            } else {
                $usuario['setores_resumo'] = 'Nenhum setor definido';
            }

            // ✅ DEBUG COMPLETO
            error_log('=== PERFIL CARREGADO (PRIMEIRO ACESSO) ===');
            error_log('Usuario ID: ' . $usuarioId);
            error_log('Empresa ID: ' . ($usuario['empresa_id'] ?? 'NULL'));
            error_log('Empresa Nome: "' . $usuario['empresa_nome'] . '"');
            error_log('Total Setores: ' . $usuario['total_setores']);
            error_log('Setores Resumo: "' . $usuario['setores_resumo'] . '"');
            error_log('Setores Array: ' . print_r($usuario['setores_nomes'], true));
            error_log('Sessão Empresa ID: ' . ($_SESSION['empresa_id'] ?? 'NULL'));
            error_log('==========================================');

            $this->render('perfil/index', ['usuario' => $usuario]);
        } catch (Exception $e) {
            error_log('Erro crítico no perfil: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            set_flash_message('error', 'Erro ao carregar perfil.');
            redirect('dashboard');
        }
    }

    /**
     * Atualizar dados pessoais (nome + sobrenome)
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
            $sobrenome = trim($_POST['sobrenome'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');

            // Validações
            if (empty($nome)) {
                set_flash_message('error', 'O nome é obrigatório.');
                redirect('perfil');
                exit;
            }

            if (empty($sobrenome)) {
                set_flash_message('error', 'O sobrenome é obrigatório.');
                redirect('perfil');
                exit;
            }

            if (strlen($nome) < 2) {
                set_flash_message('error', 'O nome deve ter pelo menos 2 caracteres.');
                redirect('perfil');
                exit;
            }

            if (strlen($sobrenome) < 2) {
                set_flash_message('error', 'O sobrenome deve ter pelo menos 2 caracteres.');
                redirect('perfil');
                exit;
            }

            // Concatenar nome e sobrenome
            $nomeCompleto = $nome . ' ' . $sobrenome;

            // Atualizar no banco
            $sql = "UPDATE usuarios SET nome = :nome, cargo = :cargo, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $this->usuarioModel->getDb()->prepare($sql);
            $result = $stmt->execute([
                'nome' => $nomeCompleto,
                'cargo' => $cargo ?: null,
                'id' => $usuarioId
            ]);

            if ($result && $stmt->rowCount() > 0) {
                // Atualizar sessão
                $_SESSION['user_name'] = $nomeCompleto;

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
     * ✅ CORREÇÃO DEFINITIVA: Verificar senha atual via AJAX
     */
    public function verificarSenhaAtual()
    {
        // Forçar header JSON
        header('Content-Type: application/json; charset=utf-8');

        // Verificar se é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        try {
            // Verificar autenticação
            if (!is_authenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }

            $usuarioId = get_user_id();

            // Obter senha do POST ou JSON
            $senhaAtual = '';
            if (isset($_POST['senha_atual'])) {
                $senhaAtual = $_POST['senha_atual'];
            } else {
                // Tentar ler JSON
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                $senhaAtual = $data['senha_atual'] ?? '';
            }

            if (empty($senhaAtual)) {
                echo json_encode(['success' => false, 'message' => 'Senha não informada']);
                exit;
            }

            // Buscar usuário no banco
            $db = $this->usuarioModel->getDb();
            $sql = "SELECT id, senha FROM usuarios WHERE id = :id AND ativo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }

            // Verificar senha
            $senhaCorreta = password_verify($senhaAtual, $usuario['senha']);

            echo json_encode([
                'success' => $senhaCorreta,
                'message' => $senhaCorreta ? 'Senha correta' : 'Senha incorreta'
            ]);
        } catch (Exception $e) {
            error_log('Erro ao verificar senha atual: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }

        exit;
    }

    /**
     * Alterar senha (sem alterações)
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

            // Verificar senha atual
            if (!password_verify($senhaAtual, $usuario['senha'])) {
                set_flash_message('error', 'Senha atual incorreta.');
                redirect('perfil');
                exit;
            }

            // Verificar se a nova senha é diferente da atual
            if (password_verify($novaSenha, $usuario['senha'])) {
                set_flash_message('error', 'A nova senha deve ser diferente da atual.');
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

                // Limpar sessão atual
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
     * Desativar conta (sem alterações)
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

            if (!password_verify($senha, $usuario['senha'])) {
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

                // Limpar sessão
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
