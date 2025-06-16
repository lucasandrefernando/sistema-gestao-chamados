<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Redefinir Senha - Sistema de Gestão de Chamados</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/css/auth.css') ?>">
    <style>
        /* Estilos para o medidor de força de senha */
        .password-strength {
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }

        .strength-meter {
            height: 4px;
            background-color: var(--gray-200);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-meter-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0;
        }

        .strength-text {
            font-size: 0.75rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .strength-text i {
            font-size: 0.875rem;
        }

        /* Cores para diferentes níveis de força */
        .strength-weak {
            background-color: var(--danger);
            width: 25%;
        }

        .strength-medium {
            background-color: var(--warning);
            width: 50%;
        }

        .strength-good {
            background-color: var(--success);
            width: 75%;
        }

        .strength-strong {
            background-color: var(--success);
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-background"></div>

        <div class="auth-wrapper">
            <div class="auth-logo-wrapper">
                <div class="auth-logo-container">
                    <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" class="auth-logo">
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-card-header"></div>

                <div class="auth-content">
                    <div class="auth-header">
                        <h1 class="auth-title">Redefinir Senha</h1>
                        <p class="auth-subtitle">Crie uma nova senha segura para sua conta</p>
                    </div>

                    <!-- Mensagens de alerta -->
                    <?php if (has_flash_message('error')): ?>
                        <div class="auth-alert auth-alert-error">
                            <i class="fas fa-exclamation-circle auth-alert-icon"></i>
                            <div><?= get_flash_message('error') ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Informações sobre o processo -->
                    <div class="auth-alert auth-alert-info">
                        <i class="fas fa-shield-alt auth-alert-icon"></i>
                        <div>Para sua segurança, crie uma senha forte com pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e símbolos.</div>
                    </div>

                    <!-- Formulário de redefinição de senha -->
                    <form action="<?= base_url('auth/processarRedefinirSenha') ?>" method="post" class="auth-form" id="resetForm">
                        <input type="hidden" name="token" value="<?= $token ?>">

                        <div class="form-group">
                            <label for="senha">Nova Senha</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="senha" name="senha" placeholder="Digite sua nova senha" required minlength="8">
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-meter">
                                    <div class="strength-meter-fill" id="strengthMeter"></div>
                                </div>
                                <div class="strength-text" id="strengthText">
                                    <i class="fas fa-circle-notch"></i> Força da senha: Digite sua senha
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Senha</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua nova senha" required minlength="8">
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-btn" id="resetButton">
                            <i class="fas fa-key"></i> Redefinir Senha
                        </button>

                        <a href="<?= base_url('auth') ?>" class="back-link">
                            <i class="fas fa-arrow-left"></i> Voltar para o login
                        </a>
                    </form>

                    <!-- Rodapé -->
                    <div class="auth-footer">
                        <div class="auth-support">
                            <i class="fas fa-headset"></i>
                            <span>Suporte: <a href="mailto:suporte@eagletelecom.com.br">suporte@eagletelecom.com.br</a></span>
                        </div>
                        <div class="auth-copyright">
                            &copy; <?= date('Y') ?> Eagle Telecom - Todos os direitos reservados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('public/js/auth.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const senhaInput = document.getElementById('senha');
            const confirmarSenhaInput = document.getElementById('confirmar_senha');
            const strengthMeter = document.getElementById('strengthMeter');
            const strengthText = document.getElementById('strengthText');
            const resetForm = document.getElementById('resetForm');
            const resetButton = document.getElementById('resetButton');

            // Verificar força da senha
            senhaInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = '';
                let icon = '';

                // Critérios de força
                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
                if (password.match(/\d/)) strength += 1;
                if (password.match(/[^a-zA-Z\d]/)) strength += 1;

                // Atualizar visual baseado na força
                strengthMeter.className = 'strength-meter-fill';

                if (password.length === 0) {
                    strengthMeter.style.width = '0%';
                    icon = '<i class="fas fa-circle-notch"></i>';
                    feedback = 'Digite sua senha';
                } else if (strength === 1) {
                    strengthMeter.classList.add('strength-weak');
                    icon = '<i class="fas fa-exclamation-circle" style="color: var(--danger)"></i>';
                    feedback = 'Fraca';
                } else if (strength === 2) {
                    strengthMeter.classList.add('strength-medium');
                    icon = '<i class="fas fa-exclamation-triangle" style="color: var(--warning)"></i>';
                    feedback = 'Média';
                } else if (strength === 3) {
                    strengthMeter.classList.add('strength-good');
                    icon = '<i class="fas fa-check-circle" style="color: var(--success)"></i>';
                    feedback = 'Boa';
                } else if (strength === 4) {
                    strengthMeter.classList.add('strength-strong');
                    icon = '<i class="fas fa-shield-alt" style="color: var(--success)"></i>';
                    feedback = 'Forte';
                }

                strengthText.innerHTML = icon + ' Força da senha: ' + feedback;

                // Verificar se as senhas coincidem
                if (confirmarSenhaInput.value && confirmarSenhaInput.value !== this.value) {
                    confirmarSenhaInput.setCustomValidity('As senhas não coincidem');
                } else {
                    confirmarSenhaInput.setCustomValidity('');
                }
            });

            // Verificar se as senhas coincidem
            confirmarSenhaInput.addEventListener('input', function() {
                if (this.value !== senhaInput.value) {
                    this.setCustomValidity('As senhas não coincidem');
                } else {
                    this.setCustomValidity('');
                }
            });

            if (resetForm) {
                resetForm.addEventListener('submit', function(e) {
                    // Verificar se as senhas coincidem
                    if (senhaInput.value !== confirmarSenhaInput.value) {
                        e.preventDefault();
                        alert('As senhas não coincidem');
                        return false;
                    }

                    // Desabilita o botão para evitar múltiplos envios
                    resetButton.disabled = true;
                    resetButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processando...';

                    // Continua com o envio do formulário
                    return true;
                });
            }
        });
    </script>
</body>

</html>