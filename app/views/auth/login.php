<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Sistema de Gestão de Chamados</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/css/auth.css') ?>">
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
                        <h1 class="auth-title">Bem-vindo ao Sistema</h1>
                        <p class="auth-subtitle">Faça login para acessar o painel de gestão de chamados</p>
                    </div>

                    <!-- Mensagens de alerta -->
                    <?php if (has_flash_message('error')): ?>
                        <div class="auth-alert auth-alert-error">
                            <i class="fas fa-exclamation-circle auth-alert-icon"></i>
                            <div><?= get_flash_message('error') ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash_message('success')): ?>
                        <div class="auth-alert auth-alert-success">
                            <i class="fas fa-check-circle auth-alert-icon"></i>
                            <div><?= get_flash_message('success') ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de login -->
                    <form action="<?= base_url('auth/login') ?>" method="post" class="auth-form" id="loginForm">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="label-row">
                                <label for="senha">Senha</label>
                                <a href="<?= base_url('auth/recuperarSenha') ?>" class="forgot-link">Esqueceu a senha?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="empresa_id">Empresa</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-building"></i></span>
                                <select id="empresa_id" name="empresa_id" required>
                                    <option value="">Selecione sua empresa</option>
                                    <?php if (isset($empresas) && !empty($empresas)): ?>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="auth-btn" id="loginButton">
                            <i class="fas fa-sign-in-alt"></i> Entrar no Sistema
                        </button>
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
</body>

</html>