<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Sistema de Gestão de Chamados</title>
    <!-- Fontes e ícones -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Estilos da aplicação -->
    <link rel="stylesheet" href="<?= base_url('public/css/auth.css') ?>">
</head>

<body>
    <div class="auth-container">
        <!-- Fundo decorativo -->
        <div class="auth-background"></div>

        <div class="auth-wrapper">
            <!-- Card principal -->
            <div class="auth-card">
                <!-- Barra superior colorida -->
                <div class="auth-card-header"></div>

                <div class="auth-content">
                    <!-- Container do logo -->
                    <div class="auth-logo-wrapper">
                        <div class="auth-logo-container">
                            <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" class="auth-logo">
                        </div>
                    </div>

                    <!-- Cabeçalho com título e subtítulo -->
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

                    <!-- Mensagem para admin master (inicialmente oculta) -->
                    <div id="adminMasterMessage" class="admin-master-message">
                        <i class="fas fa-shield-alt"></i> Você foi identificado como Administrador Master. Não é necessário selecionar uma empresa.
                    </div>

                    <!-- Mensagem para usuário em múltiplas empresas (inicialmente oculta) -->
                    <div id="multiEmpresaMessage" class="multi-empresa-message">
                        <i class="fas fa-building"></i> <span id="multiEmpresaText">Você está cadastrado em múltiplas empresas. Selecione uma para continuar.</span>
                    </div>

                    <!-- Formulário de login -->
                    <form action="<?= base_url('auth/login') ?>" method="post" class="auth-form" id="loginForm">
                        <!-- Campo oculto para indicar se é admin master -->
                        <input type="hidden" id="isAdminMaster" name="is_admin_master" value="false">

                        <!-- Campo de email -->
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                            </div>
                            <!-- Indicador de carregamento para busca de empresas -->
                            <div id="emailLoadingIndicator" class="loading-indicator">
                                <div class="loading-spinner">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </div>
                                <span>Verificando usuário...</span>
                            </div>
                        </div>

                        <!-- Campo de senha -->
                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="forgot-password-link">
                                <a href="<?= base_url('auth/recuperarSenha') ?>" class="forgot-link">Esqueceu a senha?</a>
                            </div>
                        </div>

                        <!-- Campo de seleção de empresa -->
                        <div class="form-group" id="empresaGroup">
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

                        <!-- Botão de login -->
                        <button type="submit" class="auth-btn" id="loginButton">
                            <i class="fas fa-sign-in-alt"></i> Entrar no Sistema
                        </button>
                    </form>

                    <!-- Rodapé -->
                    <div class="auth-footer">
                        <div class="auth-support">
                            <i class="fas fa-headset"></i>
                            <span>Suporte: <a href="mailto:atendimento@eagletelecom.com.br">atendimento@eagletelecom.com.br</a></span>
                        </div>
                        <div class="auth-copyright">
                            &copy; <?= date('Y') ?> Eagle Telecom - Todos os direitos reservados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script principal da autenticação -->
    <script src="<?= base_url('public/js/auth.js') ?>"></script>
</body>

</html>