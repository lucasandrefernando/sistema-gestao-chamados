<div class="user-restore-container">
    <!-- Cabeçalho da página -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-title">
                <h1>
                    <i class="fas fa-user-check"></i>
                    Restaurar Usuário
                </h1>
                <p class="subtitle">
                    Confirme a restauração do usuário removido
                </p>
            </div>
            <div class="header-actions">
                <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="restore-card">
        <div class="restore-card-header">
            <h2>Confirmar Restauração de Usuário</h2>
        </div>

        <div class="restore-card-body">
            <!-- Perfil do usuário -->
            <div class="user-profile">
                <div class="user-profile-avatar">
                    <div class="avatar-circle <?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'admin-master' : 'admin') : 'user' ?>">
                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                    </div>
                </div>
                <div class="user-profile-info">
                    <h3><?= $usuario['nome'] ?></h3>
                    <p><?= $usuario['email'] ?></p>
                    <div class="user-profile-badges">
                        <span class="badge removed">Removido</span>
                        <?php if ($usuario['admin']): ?>
                            <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                <span class="badge admin-master">Admin Master</span>
                            <?php else: ?>
                                <span class="badge admin">Admin</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge user">Usuário</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Alerta informativo -->
            <div class="info-box">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Informações Importantes</h4>
                    <p>Este usuário foi removido em <strong><?= date('d/m/Y \à\s H:i', strtotime($usuario['data_remocao'])) ?></strong>.</p>
                    <p>Ao restaurar este usuário, ele voltará a ter acesso ao sistema com as mesmas permissões anteriores.</p>
                </div>
            </div>

            <!-- Cards de informações -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-user-cog"></i>
                        <h4>Detalhes do Usuário</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">ID:</span>
                                <span class="info-value"><?= $usuario['id'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Cargo:</span>
                                <span class="info-value"><?= $usuario['cargo'] ?? 'Não definido' ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Empresa:</span>
                                <span class="info-value"><?= $_SESSION['empresa_nome'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipo:</span>
                                <span class="info-value">
                                    <?php if ($usuario['admin']): ?>
                                        <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                            <span class="badge admin-master">Administrador Master</span>
                                        <?php else: ?>
                                            <span class="badge admin">Administrador Regular</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge user">Usuário Comum</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-key"></i>
                        <h4>Licenças</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="info-list">
                            <?php if (isset($licencasInfo)): ?>
                                <div class="info-item">
                                    <span class="info-label">Total de Licenças:</span>
                                    <span class="info-value"><?= $licencasInfo['total'] ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Licenças Utilizadas:</span>
                                    <span class="info-value"><?= $licencasInfo['utilizadas'] ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Licenças Disponíveis:</span>
                                    <span class="info-value">
                                        <?php if ($licencasInfo['disponiveis'] > 0): ?>
                                            <span class="badge available"><?= $licencasInfo['disponiveis'] ?> disponíveis</span>
                                        <?php else: ?>
                                            <span class="badge unavailable">Nenhuma disponível</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <p class="no-info">Informações de licenças não disponíveis.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
                <div class="alert danger">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h5>Não há licenças disponíveis!</h5>
                        <p>Não é possível restaurar este usuário porque não há licenças disponíveis.</p>
                        <?php if (is_admin_master()): ?>
                            <div class="alert-actions">
                                <a href="<?= base_url('licencas/criar') ?>" class="btn-primary">
                                    <i class="fas fa-plus-circle"></i> Criar Nova Licença
                                </a>
                            </div>
                        <?php else: ?>
                            <p>Por favor, solicite ao administrador master que crie novas licenças.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="restore-actions">
                    <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn-outline">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a href="<?= base_url('usuarios/restaurar/' . $usuario['id']) ?>" class="btn-success">
                        <i class="fas fa-user-check"></i> Confirmar Restauração
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>