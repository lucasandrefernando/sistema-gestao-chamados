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
                        <i class="fas fa-key"></i>
                    </div>
                    <h2>Recuperação de Senha</h2>
                    <p>Informe seus dados para receber instruções</p>
                </div>

                <form action="<?= base_url('auth/processarRecuperarSenha') ?>" method="post" class="auth-form">
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

                    <div class="form-group mb-4">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg auth-btn">
                            <i class="fas fa-paper-plane me-2"></i> Enviar Instruções
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="<?= base_url('auth') ?>" class="auth-back-link">
                            <i class="fas fa-arrow-left me-1"></i> Voltar para o login
                        </a>
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

        <!-- Informações do processo -->
        <div class="auth-info">
            <div class="auth-recovery-steps">
                <h3 class="auth-recovery-title">Processo de Recuperação</h3>

                <div class="auth-step active">
                    <div class="auth-step-number">1</div>
                    <div class="auth-step-content">
                        <h4>Solicitar Recuperação</h4>
                        <p>Informe sua empresa e e-mail cadastrado</p>
                    </div>
                </div>

                <div class="auth-step">
                    <div class="auth-step-number">2</div>
                    <div class="auth-step-content">
                        <h4>Verificar E-mail</h4>
                        <p>Acesse o link enviado para seu e-mail</p>
                    </div>
                </div>

                <div class="auth-step">
                    <div class="auth-step-number">3</div>
                    <div class="auth-step-content">
                        <h4>Definir Nova Senha</h4>
                        <p>Crie uma nova senha segura para sua conta</p>
                    </div>
                </div>

                <div class="auth-step">
                    <div class="auth-step-number">4</div>
                    <div class="auth-step-content">
                        <h4>Acessar o Sistema</h4>
                        <p>Faça login com sua nova senha</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>