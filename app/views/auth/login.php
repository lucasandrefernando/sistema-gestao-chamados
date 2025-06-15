<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Cabeçalho com logo e título -->
            <div class="auth-header">
                <div class="auth-brand">
                    <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" class="auth-logo">
                </div>
                <h1 class="auth-title">Sistema de Gestão de Chamados</h1>
                <div class="auth-subtitle">Hospital Madre Teresa</div>
            </div>

            <!-- Corpo do formulário -->
            <div class="auth-body">
                <div class="auth-welcome">
                    <div class="auth-welcome-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h2>Bem-vindo</h2>
                    <p>Faça login para acessar o sistema</p>
                </div>

                <form action="<?= base_url('auth/login') ?>" method="post" class="auth-form">
                    <div class="form-group mb-3">
                        <label for="empresa_id" class="form-label">Empresa</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <select class="form-select" id="empresa_id" name="empresa_id" required>
                                <option value="">Selecione uma empresa</option>
                                <?php if (isset($empresas) && !empty($empresas)): ?>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="senha" class="form-label">Senha</label>
                            <a href="<?= base_url('auth/recuperarSenha') ?>" class="auth-link-small">Esqueceu?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                            <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg auth-btn">
                            <i class="fas fa-sign-in-alt me-2"></i> Entrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rodapé -->
            <div class="auth-footer">
                <div class="auth-support">
                    <i class="fas fa-info-circle me-1"></i> Suporte: <a href="mailto:suporte@eagletelecom.com.br">suporte@eagletelecom.com.br</a>
                </div>
                <div class="auth-copyright">
                    &copy; <?= date('Y') ?> Eagle Telecom - Todos os direitos reservados
                </div>
            </div>
        </div>

        <!-- Informações do sistema -->
        <div class="auth-info">
            <div class="auth-info-item">
                <div class="auth-info-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="auth-info-content">
                    <h3>Gestão Eficiente</h3>
                    <p>Acompanhe e gerencie todos os chamados técnicos em um só lugar</p>
                </div>
            </div>

            <div class="auth-info-item">
                <div class="auth-info-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="auth-info-content">
                    <h3>Relatórios Detalhados</h3>
                    <p>Visualize estatísticas e métricas para otimizar o atendimento</p>
                </div>
            </div>

            <div class="auth-info-item">
                <div class="auth-info-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="auth-info-content">
                    <h3>Colaboração em Equipe</h3>
                    <p>Trabalhe em conjunto com sua equipe para resolver chamados</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle de visibilidade da senha
        const toggleButton = document.querySelector('.password-toggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                const input = document.getElementById('senha');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    });
</script>