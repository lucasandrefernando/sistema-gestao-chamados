<?php
require_once ROOT_DIR . '/app/models/Model.php';

// Importações do PHPMailer (caminho manual)
require_once ROOT_DIR . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once ROOT_DIR . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once ROOT_DIR . '/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Resto do código...
/**
 * Serviço para envio de e-mails usando PHPMailer
 */
class EmailService extends Model
{
    private $mailer;

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();

        // Inicializa o PHPMailer
        $this->mailer = new PHPMailer(true);

        // Configurações básicas
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isHTML(true);

        // Configurações de SMTP
        $this->configurarSMTP();
    }

    /**
     * Configura as opções de SMTP
     */
    private function configurarSMTP()
    {
        // Verifica se as constantes estão definidas no config/app.php
        if (defined('EMAIL_SMTP_ENABLED') && EMAIL_SMTP_ENABLED) {
            $this->mailer->isSMTP();
            $this->mailer->Host = defined('EMAIL_SMTP_HOST') ? EMAIL_SMTP_HOST : 'smtp.gmail.com';
            $this->mailer->SMTPAuth = defined('EMAIL_SMTP_AUTH') ? EMAIL_SMTP_AUTH : true;
            $this->mailer->Username = defined('EMAIL_SMTP_USERNAME') ? EMAIL_SMTP_USERNAME : '';
            $this->mailer->Password = defined('EMAIL_SMTP_PASSWORD') ? EMAIL_SMTP_PASSWORD : '';
            $this->mailer->SMTPSecure = defined('EMAIL_SMTP_SECURE') ? EMAIL_SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = defined('EMAIL_SMTP_PORT') ? EMAIL_SMTP_PORT : 587;

            // Debug (opcional, remover em produção)
            if (defined('EMAIL_SMTP_DEBUG') && EMAIL_SMTP_DEBUG) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
        }
    }

    /**
     * Envia um e-mail
     * 
     * @param string $para Email do destinatário
     * @param string $assunto Assunto do email
     * @param string $mensagem Corpo do email em HTML
     * @param array $anexos Array de anexos (opcional)
     * @return array Retorna array com status e mensagem
     */
    public function enviar($para, $assunto, $mensagem, $anexos = [])
    {
        try {
            // Limpa destinatários anteriores
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();

            // Remetente
            $de = defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@eagletelecom.com.br';
            $deNome = defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'Sistema de Gestão de Chamados';
            $this->mailer->setFrom($de, $deNome);

            // Responder para
            $replyTo = defined('EMAIL_REPLY_TO') ? EMAIL_REPLY_TO : $de;
            $this->mailer->addReplyTo($replyTo, $deNome);

            // Destinatário
            $this->mailer->addAddress($para);

            // Assunto e corpo
            $this->mailer->Subject = $assunto;
            $this->mailer->Body = $mensagem;
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $mensagem));

            // Adiciona anexos, se houver
            if (!empty($anexos) && is_array($anexos)) {
                foreach ($anexos as $anexo) {
                    if (isset($anexo['path']) && file_exists($anexo['path'])) {
                        $nome = isset($anexo['name']) ? $anexo['name'] : basename($anexo['path']);
                        $this->mailer->addAttachment($anexo['path'], $nome);
                    }
                }
            }

            // Envia o e-mail
            $enviado = $this->mailer->send();

            // Registra o sucesso
            error_log("Email enviado com sucesso para: {$para}");

            return [
                'success' => true,
                'message' => 'E-mail enviado com sucesso'
            ];
        } catch (Exception $e) {
            // Registra o erro
            error_log("Falha ao enviar email para {$para}: " . $this->mailer->ErrorInfo);

            return [
                'success' => false,
                'message' => 'Falha ao enviar e-mail: ' . $this->mailer->ErrorInfo,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envia um e-mail de recuperação de senha
     * 
     * @param string $para Email do destinatário
     * @param string $nome Nome do destinatário
     * @param string $token Token de recuperação
     * @return array Retorna array com status e mensagem
     */
    public function enviarRecuperacaoSenha($para, $nome, $token)
    {
        $assunto = 'Recuperação de Senha - Sistema de Gestão de Chamados';

        $resetUrl = base_url("auth/redefinirSenha?token={$token}");
        $currentYear = date('Y');

        // Data e hora da expiração (1 hora após o envio)
        $expiraEm = date('d/m/Y \à\s H:i', strtotime('+1 hour'));

        $mensagem = <<<HTML
                                <!DOCTYPE html>
                                <html lang="pt-BR">
                                <head>
                                    <meta charset="UTF-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <title>Recuperação de Senha</title>
                                    <style>
                                        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                                        
                                        body {
                                            font-family: 'Inter', Arial, sans-serif;
                                            line-height: 1.6;
                                            color: #333;
                                            margin: 0;
                                            padding: 0;
                                            background-color: #f5f7fa;
                                        }
                                        
                                        .email-container {
                                            max-width: 600px;
                                            margin: 0 auto;
                                            background-color: #ffffff;
                                            border-radius: 8px;
                                            overflow: hidden;
                                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                                        }
                                        
                                        .email-header {
                                            background-color: #2563eb;
                                            padding: 25px 20px;
                                            text-align: center;
                                        }
                                        
                                        .header-title {
                                            color: #ffffff;
                                            font-size: 24px;
                                            font-weight: 700;
                                            margin: 0;
                                            letter-spacing: 0.5px;
                                            text-transform: uppercase;
                                        }
                                        
                                        .email-body {
                                            padding: 30px;
                                            background-color: #ffffff;
                                        }
                                        
                                        .email-title {
                                            font-size: 24px;
                                            font-weight: 700;
                                            color: #2563eb;
                                            margin-top: 0;
                                            margin-bottom: 20px;
                                            text-align: center;
                                        }
                                        
                                        .greeting {
                                            font-size: 18px;
                                            font-weight: 600;
                                            margin-bottom: 20px;
                                        }
                                        
                                        .message {
                                            margin-bottom: 25px;
                                            color: #4b5563;
                                        }
                                        
                                        .button-container {
                                            text-align: center;
                                            margin: 30px 0;
                                        }
                                        
                                        .button {
                                            display: inline-block;
                                            background-color: #2563eb;
                                            color: #ffffff !important;
                                            text-decoration: none;
                                            padding: 14px 30px;
                                            border-radius: 6px;
                                            font-weight: 600;
                                            font-size: 16px;
                                            transition: background-color 0.3s;
                                        }
                                        
                                        .button:hover {
                                            background-color: #1d4ed8;
                                        }
                                        
                                        .link-container {
                                            background-color: #f3f4f6;
                                            padding: 15px;
                                            border-radius: 6px;
                                            margin-bottom: 25px;
                                            word-break: break-all;
                                        }
                                        
                                        .link {
                                            color: #2563eb;
                                            font-family: monospace;
                                            font-size: 14px;
                                        }
                                        
                                        .expiry-notice {
                                            background-color: #fffbeb;
                                            border-left: 4px solid #f59e0b;
                                            padding: 15px;
                                            margin-bottom: 25px;
                                            color: #92400e;
                                        }
                                        
                                        .security-notice {
                                            background-color: #f8fafc;
                                            border: 1px solid #e2e8f0;
                                            padding: 15px;
                                            margin-bottom: 25px;
                                            border-radius: 6px;
                                        }
                                        
                                        .security-notice h3 {
                                            margin-top: 0;
                                            color: #334155;
                                            font-size: 16px;
                                        }
                                        
                                        .security-notice ul {
                                            margin-bottom: 0;
                                            padding-left: 20px;
                                        }
                                        
                                        .steps {
                                            margin-bottom: 25px;
                                        }
                                        
                                        .step {
                                            padding: 12px 15px;
                                            margin-bottom: 10px;
                                            background-color: #f8fafc;
                                            border-radius: 6px;
                                            border-left: 3px solid #2563eb;
                                        }
                                        
                                        .step-title {
                                            font-weight: 600;
                                            color: #1e40af;
                                            margin-bottom: 5px;
                                        }
                                        
                                        .step-description {
                                            color: #4b5563;
                                            margin: 0;
                                        }
                                        
                                        .email-footer {
                                            background-color: #f8fafc;
                                            padding: 20px;
                                            text-align: center;
                                            color: #64748b;
                                            font-size: 14px;
                                            border-top: 1px solid #e2e8f0;
                                        }
                                        
                                        .footer-links {
                                            margin-bottom: 10px;
                                        }
                                        
                                        .footer-links a {
                                            color: #2563eb;
                                            text-decoration: none;
                                            margin: 0 10px;
                                        }
                                        
                                        .footer-links a:hover {
                                            text-decoration: underline;
                                        }
                                        
                                        .divider {
                                            height: 1px;
                                            background-color: #e2e8f0;
                                            margin: 20px 0;
                                        }
                                        
                                        @media only screen and (max-width: 600px) {
                                            .email-body {
                                                padding: 20px;
                                            }
                                            
                                            .email-title {
                                                font-size: 20px;
                                            }
                                            
                                            .button {
                                                display: block;
                                                text-align: center;
                                            }
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class="email-container">
                                        <div class="email-header">
                                            <h1 class="header-title">Sistema de Gestão de Chamados</h1>
                                        </div>
                                        
                                        <div class="email-body">
                                            <h1 class="email-title">Recuperação de Senha</h1>
                                            
                                            <p class="greeting">Olá, <strong>{$nome}</strong>!</p>
                                            
                                            <p class="message">Recebemos uma solicitação para redefinir a senha da sua conta no Sistema de Gestão de Chamados. Para criar uma nova senha, siga as instruções abaixo:</p>
                                            
                                            <div class="steps">
                                                <div class="step">
                                                    <div class="step-title">Passo 1: Acesse o link de recuperação</div>
                                                    <p class="step-description">Clique no botão "Redefinir Minha Senha" abaixo.</p>
                                                </div>
                                                
                                                <div class="step">
                                                    <div class="step-title">Passo 2: Acesse a página segura</div>
                                                    <p class="step-description">Você será direcionado para uma página segura onde poderá criar uma nova senha.</p>
                                                </div>
                                                
                                                <div class="step">
                                                    <div class="step-title">Passo 3: Crie uma senha forte</div>
                                                    <p class="step-description">Use pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e símbolos.</p>
                                                </div>
                                                
                                                <div class="step">
                                                    <div class="step-title">Passo 4: Faça login com sua nova senha</div>
                                                    <p class="step-description">Após redefinir sua senha, você poderá acessar o sistema com suas novas credenciais.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="button-container">
                                                <a href="{$resetUrl}" class="button">Redefinir Minha Senha</a>
                                            </div>
                                            
                                            <p>Se o botão acima não funcionar, copie e cole o link abaixo no seu navegador:</p>
                                            
                                            <div class="link-container">
                                                <a href="{$resetUrl}" class="link">{$resetUrl}</a>
                                            </div>
                                            
                                            <div class="expiry-notice">
                                                <strong>⚠️ Atenção:</strong> Este link é válido até <strong>{$expiraEm}</strong> e pode ser usado apenas uma vez.
                                            </div>
                                            
                                            <div class="security-notice">
                                                <h3>🔒 Dicas de Segurança:</h3>
                                                <ul>
                                                    <li>Nunca compartilhe sua senha com outras pessoas</li>
                                                    <li>Crie uma senha única para o sistema, diferente das que você usa em outros sites</li>
                                                    <li>Evite usar informações pessoais óbvias em sua senha</li>
                                                    <li>Considere usar um gerenciador de senhas para maior segurança</li>
                                                </ul>
                                            </div>
                                            
                                            <p class="message">Se você não solicitou esta recuperação de senha, por favor ignore este e-mail ou entre em contato com o suporte técnico imediatamente, pois alguém pode estar tentando acessar sua conta.</p>
                                            
                                            <div class="divider"></div>
                                            
                                            <p style="text-align: center; color: #64748b;">Precisa de ajuda? Entre em contato com nosso suporte.</p>
                                        </div>
                                        
                                        <div class="email-footer">
                                            <div class="footer-links">
                                                <a href="mailto:suporte@eagletelecom.com.br">Suporte</a>
                                                <a href="https://www.eagletelecom.com.br">Website</a>
                                                <a href="tel:+551140028922">Contato</a>
                                            </div>
                                            
                                            <p>&copy; {$currentYear} Eagle Telecom - Todos os direitos reservados</p>
                                            <p>Este é um e-mail automático, por favor não responda.</p>
                                        </div>
                                    </div>
                                </body>
                                </html>
                                HTML;

                                        return $this->enviar($para, $assunto, $mensagem);
                                    }
                                }
