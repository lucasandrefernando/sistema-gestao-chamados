<?php
$pageTitle = 'Meu Perfil';
require_once ROOT_DIR . '/app/views/templates/header.php';

// ✅ CORREÇÃO: Debug na view para verificar dados recebidos
error_log('=== VIEW DEBUG ===');
error_log('Usuario recebido na view: ' . (isset($usuario) ? 'SIM' : 'NÃO'));
if (isset($usuario)) {
    error_log('Empresa nome na view: "' . ($usuario['empresa_nome'] ?? 'NÃO DEFINIDO') . '"');
    error_log('Total setores na view: ' . ($usuario['total_setores'] ?? 'NÃO DEFINIDO'));
    error_log('Setores resumo na view: "' . ($usuario['setores_resumo'] ?? 'NÃO DEFINIDO') . '"');
}
error_log('==================');

// Verificar se $usuario existe e tem dados necessários
if (!isset($usuario) || empty($usuario)) {
    error_log('ERRO: Usuario não definido na view, redirecionando...');
    header('Location: ' . base_url('login'));
    exit;
}

// ✅ CORREÇÃO: Garantir que as variáveis existam na view
if (!isset($usuario['empresa_nome'])) {
    error_log('AVISO: empresa_nome não definido na view');
    $usuario['empresa_nome'] = 'Empresa não encontrada';
}

if (!isset($usuario['total_setores'])) {
    error_log('AVISO: total_setores não definido na view');
    $usuario['total_setores'] = 0;
}

if (!isset($usuario['setores_resumo'])) {
    error_log('AVISO: setores_resumo não definido na view');
    $usuario['setores_resumo'] = 'Nenhum setor definido';
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

// Separar nome e sobrenome
$primeiroNome = $nomes[0] ?? '';
$sobrenome = '';
if (count($nomes) > 1) {
    $sobrenome = implode(' ', array_slice($nomes, 1));
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
                        <span class="stat-value">
                            <?php
                            // ✅ CORREÇÃO: Verificação múltipla
                            if (isset($usuario['empresa_nome']) && !empty($usuario['empresa_nome'])) {
                                echo htmlspecialchars($usuario['empresa_nome']);
                            } else {
                                echo 'Empresa não definida';
                                // Debug na view
                                error_log('VIEW DEBUG: empresa_nome não definido. Dados: ' . print_r($usuario, true));
                            }
                            ?>
                        </span>
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
                            <label>Setores</label>
                            <div class="field-value">
                                <i class="fas fa-sitemap"></i>
                                <span>
                                    <?php
                                    // ✅ CORREÇÃO: Verificação múltipla para setores
                                    if (isset($usuario['total_setores']) && $usuario['total_setores'] > 0): ?>
                                        <span class="setores-resumo"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSetores"
                                            style="cursor: pointer; color: var(--profile-primary); text-decoration: underline;">
                                            <?= htmlspecialchars($usuario['setores_resumo'] ?? 'Setores disponíveis') ?>
                                            <?php if ($usuario['total_setores'] > 1): ?>
                                                <i class="fas fa-external-link-alt" style="font-size: 0.8rem; margin-left: 0.3rem;"></i>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--profile-text-light);">
                                            <?php
                                            // Debug na view para setores
                                            if (!isset($usuario['total_setores'])) {
                                                error_log('VIEW DEBUG: total_setores não definido');
                                            }
                                            echo 'Nenhum setor definido';
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
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
                                Nome
                            </label>
                            <input type="text" class="form-control-modern" name="nome" id="nome"
                                value="<?= htmlspecialchars($primeiroNome) ?>" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="fas fa-user"></i>
                                Sobrenome
                            </label>
                            <input type="text" class="form-control-modern" name="sobrenome" id="sobrenome"
                                value="<?= htmlspecialchars($sobrenome) ?>" required>
                        </div>
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
                            <input type="password" class="form-control-modern" name="senha_atual" id="senha_atual" required>
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="field-feedback" id="senha-atual-feedback"></div>
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
                        <div class="field-feedback" id="confirmar-senha-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-new" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-warning-new" id="btn-alterar-senha" disabled>
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

<!-- Modal: Setores do Usuário -->
<?php if (isset($usuario['total_setores']) && $usuario['total_setores'] > 0): ?>
    <div class="modal fade" id="modalSetores" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header-modern">
                    <div class="modal-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div class="modal-title-content">
                        <h5>Meus Setores</h5>
                        <p>Setores onde você está cadastrado no sistema</p>
                    </div>
                    <button type="button" class="btn-close-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body-modern">
                    <?php if (!empty($usuario['setores_detalhados'])): ?>
                        <div class="setores-grid">
                            <?php foreach ($usuario['setores_detalhados'] as $index => $setor): ?>
                                <div class="setor-card <?= $setor['principal'] ? 'setor-principal' : '' ?>">
                                    <div class="setor-icon">
                                        <i class="fas <?= $setor['principal'] ? 'fa-star' : 'fa-building' ?>"></i>
                                    </div>
                                    <div class="setor-info">
                                        <h4><?= htmlspecialchars($setor['nome']) ?></h4>
                                        <?php if ($setor['principal']): ?>
                                            <span class="setor-badge principal">
                                                <i class="fas fa-crown"></i>
                                                Setor Principal
                                            </span>
                                        <?php endif; ?>
                                        <!-- ✅ REMOVIDO: Setor Secundário -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="setores-summary">
                            <div class="summary-item">
                                <i class="fas fa-chart-pie"></i>
                                <span><strong><?= $usuario['total_setores'] ?></strong> setor<?= $usuario['total_setores'] > 1 ? 'es' : '' ?> no total</span>
                            </div>
                            <div class="summary-item">
                                <i class="fas fa-star"></i>
                                <span><strong><?= count(array_filter($usuario['setores_detalhados'], function ($s) {
                                                    return $s['principal'];
                                                })) ?></strong> setor principal</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                            <h5>Nenhum setor encontrado</h5>
                            <p class="text-muted">Você não está associado a nenhum setor no momento.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-primary-new" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i>
                        Entendi
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once ROOT_DIR . '/app/views/templates/footer.php'; ?>