<?php
$pageTitle = 'Meu Perfil';
require_once ROOT_DIR . '/app/views/templates/header.php';

// Verificar se $usuario existe e tem dados necessários
if (!isset($usuario) || empty($usuario)) {
    header('Location: ' . base_url('login'));
    exit;
}

// Gerar iniciais para avatar (com validação)
$nomes = explode(' ', trim($usuario['nome'] ?? ''));
$iniciais = '';
if (count($nomes) >= 2) {
    $iniciais = strtoupper($nomes[0][0] . $nomes[count($nomes) - 1][0]);
} else if (count($nomes) === 1 && !empty($nomes[0])) {
    $iniciais = strtoupper($nomes[0][0] . (isset($nomes[0][1]) ? $nomes[0][1] : ''));
} else {
    $iniciais = 'US'; // Fallback
}
?>

<div class="profile-dashboard">
    <!-- Sidebar do Perfil -->
    <div class="profile-sidebar">
        <div class="profile-card">
            <div class="profile-avatar-container">
                <div class="profile-avatar-main">
                    <?= $iniciais ?>
                </div>
                <div class="profile-status-indicator <?= $usuario['ativo'] ? 'active' : 'inactive' ?>"></div>
            </div>

            <div class="profile-basic-info">
                <h2 class="profile-username"><?= htmlspecialchars($usuario['nome']) ?></h2>
                <p class="profile-email"><?= htmlspecialchars($usuario['email']) ?></p>

                <div class="profile-badges">
                    <?php if ($usuario['admin']): ?>
                        <span class="badge-admin">
                            <i class="fas fa-crown"></i>
                            Admin <?= ucfirst($usuario['admin_tipo'] ?? 'Regular') ?>
                        </span>
                    <?php else: ?>
                        <span class="badge-user">
                            <i class="fas fa-user"></i>
                            Usuário
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-quick-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Último Acesso</span>
                        <span class="stat-value">
                            <?= $usuario['ultimo_acesso'] ? date('d/m/Y', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                        </span>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Empresa</span>
                        <span class="stat-value"><?= htmlspecialchars($usuario['empresa_nome'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu de Ações -->
        <div class="profile-actions-menu">
            <h3>Configurações</h3>
            <ul class="actions-list">
                <li>
                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#modalDados">
                        <i class="fas fa-user-edit"></i>
                        <span>Editar Perfil</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </li>
                <li>
                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#modalSenha">
                        <i class="fas fa-key"></i>
                        <span>Alterar Senha</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </li>
                <?php if (!($usuario['admin'] && $usuario['admin_tipo'] === 'master')): ?>
                    <li>
                        <button class="action-btn danger" data-bs-toggle="modal" data-bs-target="#modalDesativar">
                            <i class="fas fa-user-times"></i>
                            <span>Desativar Conta</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="profile-content">
        <!-- Mensagens -->
        <?php if (has_flash_message('success')): ?>
            <div class="notification success">
                <i class="fas fa-check-circle"></i>
                <span><?= get_flash_message('success') ?></span>
                <button class="close-notification">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (has_flash_message('error')): ?>
            <div class="notification error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= get_flash_message('error') ?></span>
                <button class="close-notification">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Header da Página -->
        <div class="content-header">
            <div class="header-title">
                <h1>Meu Perfil</h1>
                <p>Gerencie suas informações pessoais e configurações de conta</p>
            </div>
            <div class="header-actions">
                <button class="btn-primary-new" data-bs-toggle="modal" data-bs-target="#modalDados">
                    <i class="fas fa-edit"></i>
                    Editar Perfil
                </button>
            </div>
        </div>

        <!-- Seções de Informações -->
        <div class="info-sections">
            <!-- Informações Pessoais -->
            <section class="info-section">
                <div class="section-header">
                    <h3>
                        <i class="fas fa-user"></i>
                        Informações Pessoais
                    </h3>
                    <button class="edit-section-btn" data-bs-toggle="modal" data-bs-target="#modalDados">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>

                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-field">
                            <label>Nome Completo</label>
                            <div class="field-value">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars($usuario['nome']) ?></span>
                            </div>
                        </div>

                        <div class="info-field">
                            <label>E-mail</label>
                            <div class="field-value">
                                <i class="fas fa-envelope"></i>
                                <span><?= htmlspecialchars($usuario['email']) ?></span>
                            </div>
                        </div>

                        <div class="info-field">
                            <label>Cargo</label>
                            <div class="field-value">
                                <i class="fas fa-briefcase"></i>
                                <span><?= htmlspecialchars($usuario['cargo'] ?? 'Não informado') ?></span>
                            </div>
                        </div>

                        <div class="info-field">
                            <label>Setor</label>
                            <div class="field-value">
                                <i class="fas fa-sitemap"></i>
                                <span><?= htmlspecialchars($usuario['setor_nome'] ?? 'Não definido') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Segurança da Conta -->
            <section class="info-section">
                <div class="section-header">
                    <h3>
                        <i class="fas fa-shield-alt"></i>
                        Segurança da Conta
                    </h3>
                    <button class="edit-section-btn" data-bs-toggle="modal" data-bs-target="#modalSenha">
                        <i class="fas fa-key"></i>
                    </button>
                </div>

                <div class="section-content">
                    <div class="security-items">
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="security-info">
                                <h4>Senha</h4>
                                <p>Última alteração: Não disponível</p>
                            </div>
                            <button class="btn-outline-new" data-bs-toggle="modal" data-bs-target="#modalSenha">
                                Alterar
                            </button>
                        </div>

                        <div class="security-item">
                            <div class="security-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="security-info">
                                <h4>Status da Conta</h4>
                                <p><?= $usuario['ativo'] ? 'Conta ativa e funcionando' : 'Conta desativada' ?></p>
                            </div>
                            <span class="status-badge <?= $usuario['ativo'] ? 'active' : 'inactive' ?>">
                                <?= $usuario['ativo'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Zona de Perigo -->
            <?php if (!($usuario['admin'] && $usuario['admin_tipo'] === 'master')): ?>
                <section class="info-section danger-zone">
                    <div class="section-header">
                        <h3>
                            <i class="fas fa-exclamation-triangle"></i>
                            Zona de Perigo
                        </h3>
                    </div>

                    <div class="section-content">
                        <div class="danger-item">
                            <div class="danger-info">
                                <h4>Desativar Conta</h4>
                                <p>Desative sua conta permanentemente. Esta ação só pode ser revertida por um administrador.</p>
                            </div>
                            <button class="btn-danger-new" data-bs-toggle="modal" data-bs-target="#modalDesativar">
                                <i class="fas fa-user-times"></i>
                                Desativar Conta
                            </button>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Alterar Dados -->
<div class="modal fade" id="modalDados" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header-modern">
                <div class="modal-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="modal-title-content">
                    <h5>Editar Perfil</h5>
                    <p>Atualize suas informações pessoais</p>
                </div>
                <button type="button" class="btn-close-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formDados" action="<?= base_url('perfil/atualizar-dados') ?>" method="post">
                <div class="modal-body-modern">
                    <div class="form-row">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Nome Completo
                            </label>
                            <input type="text" class="form-control-modern" name="nome"
                                value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-briefcase"></i>
                                Cargo
                            </label>
                            <input type="text" class="form-control-modern" name="cargo"
                                value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"
                                placeholder="Ex: Analista de Sistemas">
                        </div>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-new" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary-new">
                        <i class="fas fa-save"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Alterar Senha -->
<div class="modal fade" id="modalSenha" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header-modern warning">
                <div class="modal-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="modal-title-content">
                    <h5>Alterar Senha</h5>
                    <p>Mantenha sua conta segura</p>
                </div>
                <button type="button" class="btn-close-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formSenha" action="<?= base_url('perfil/alterar-senha') ?>" method="post">
                <div class="modal-body-modern">
                    <div class="alert-modern info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Importante:</strong> Você será desconectado após alterar a senha e receberá um e-mail de confirmação.
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-lock"></i>
                            Senha Atual
                        </label>
                        <div class="password-input-group">
                            <input type="password" class="form-control-modern" name="senha_atual" required>
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-key"></i>
                            Nova Senha
                        </label>
                        <div class="password-input-group">
                            <input type="password" class="form-control-modern" name="nova_senha"
                                id="nova_senha" required minlength="8">
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <!-- Indicador de Força -->
                        <div class="password-strength-container">
                            <div class="strength-bar">
                                <div class="strength-fill"></div>
                            </div>
                            <div class="strength-text">
                                Digite sua senha para ver a força
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-check"></i>
                            Confirmar Nova Senha
                        </label>
                        <div class="password-input-group">
                            <input type="password" class="form-control-modern" name="confirmar_senha"
                                id="confirmar_senha" required minlength="8">
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-new" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-warning-new">
                        <i class="fas fa-key"></i>
                        Alterar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Desativar Conta -->
<?php if (!($usuario['admin'] && $usuario['admin_tipo'] === 'master')): ?>
    <div class="modal fade" id="modalDesativar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header-modern danger">
                    <div class="modal-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="modal-title-content">
                        <h5>Desativar Conta</h5>
                        <p>Esta ação é irreversível</p>
                    </div>
                    <button type="button" class="btn-close-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="formDesativar" action="<?= base_url('perfil/desativar') ?>" method="post">
                    <div class="modal-body-modern">
                        <div class="alert-modern danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>ATENÇÃO:</strong> Esta ação desativará sua conta permanentemente. Apenas um administrador poderá reativá-la.
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-lock"></i>
                                Confirme sua Senha
                            </label>
                            <div class="password-input-group">
                                <input type="password" class="form-control-modern" name="senha" required>
                                <button type="button" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-keyboard"></i>
                                Digite "DESATIVAR" para confirmar
                            </label>
                            <input type="text" class="form-control-modern" id="confirmacao_desativar"
                                placeholder="DESATIVAR" required>
                        </div>
                    </div>

                    <div class="modal-footer-modern">
                        <button type="button" class="btn-secondary-new" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-danger-new">
                            <i class="fas fa-user-times"></i>
                            Confirmar Desativação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once ROOT_DIR . '/app/views/templates/footer.php'; ?>