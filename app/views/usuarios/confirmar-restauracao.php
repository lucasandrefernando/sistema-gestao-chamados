<div class="page-container">
    <!-- Cabeçalho da página -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <h1 class="page-title">
                    <i class="fas fa-user-check page-title-icon"></i>
                    Restaurar Usuário
                </h1>
                <p class="page-subtitle">
                    Confirme a restauração do usuário removido
                </p>
            </div>
            <div class="page-actions">
                <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Conteúdo principal -->
    <div class="content-card">
        <div class="content-card-header">
            <div class="content-card-title">
                <h2 class="section-title">Confirmar Restauração de Usuário</h2>
            </div>
        </div>
        <div class="content-card-body">
            <div class="user-profile-header">
                <div class="user-profile-avatar">
                    <div class="user-avatar-lg bg-success">
                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                    </div>
                </div>
                <div class="user-profile-info">
                    <h3 class="user-profile-name"><?= $usuario['nome'] ?></h3>
                    <p class="user-profile-email"><?= $usuario['email'] ?></p>
                    <div class="user-profile-badges">
                        <span class="badge bg-danger">Removido</span>
                        <?php if ($usuario['admin']): ?>
                            <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                <span class="badge bg-danger">Admin Master</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary">Usuário</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="alert alert-info my-4">
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-info-circle fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading">Informações Importantes</h5>
                        <p>Este usuário foi removido em <strong><?= date('d/m/Y \à\s H:i', strtotime($usuario['data_remocao'])) ?></strong>.</p>
                        <p class="mb-0">Ao restaurar este usuário, ele voltará a ter acesso ao sistema com as mesmas permissões anteriores.</p>
                    </div>
                </div>
            </div>

            <div class="info-cards-row">
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-user-cog info-card-icon"></i>
                        <h5 class="info-card-title">Detalhes do Usuário</h5>
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
                                            <span class="badge bg-danger">Administrador Master</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Administrador Regular</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Usuário Comum</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-key info-card-icon"></i>
                        <h5 class="info-card-title">Licenças</h5>
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
                                            <span class="badge bg-success"><?= $licencasInfo['disponiveis'] ?> disponíveis</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Nenhuma disponível</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Informações de licenças não disponíveis.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
                <div class="alert alert-danger mt-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-exclamation-triangle fs-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading">Não há licenças disponíveis!</h5>
                            <p class="mb-0">Não é possível restaurar este usuário porque não há licenças disponíveis.</p>
                            <?php if (is_admin_master()): ?>
                                <hr>
                                <div class="text-end">
                                    <a href="<?= base_url('licencas/criar') ?>" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i> Criar Nova Licença
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="mb-0">Por favor, solicite ao administrador master que crie novas licenças.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-actions mt-4">
                    <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a href="<?= base_url('usuarios/restaurar/' . $usuario['id']) ?>" class="btn btn-success">
                        <i class="fas fa-user-check"></i> Confirmar Restauração
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>