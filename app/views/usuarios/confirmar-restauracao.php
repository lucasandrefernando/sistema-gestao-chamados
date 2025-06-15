<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Restaurar Usuário</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-check me-2"></i> Confirmar Restauração de Usuário
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-circle bg-success text-white mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                    </div>
                    <h4><?= $usuario['nome'] ?></h4>
                    <p class="text-muted"><?= $usuario['email'] ?></p>
                </div>

                <div class="alert alert-info">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Informações Importantes</h5>
                            <p>Este usuário foi removido em <strong><?= date('d/m/Y \à\s H:i', strtotime($usuario['data_remocao'])) ?></strong>.</p>
                            <p class="mb-0">Ao restaurar este usuário, ele voltará a ter acesso ao sistema com as mesmas permissões anteriores.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-primary">Detalhes do Usuário</h6>
                                <hr>
                                <p class="mb-2"><strong>ID:</strong> <?= $usuario['id'] ?></p>
                                <p class="mb-2"><strong>Cargo:</strong> <?= $usuario['cargo'] ?? 'Não definido' ?></p>
                                <p class="mb-2"><strong>Empresa:</strong> <?= $_SESSION['empresa_nome'] ?></p>
                                <p class="mb-0">
                                    <strong>Tipo:</strong>
                                    <?php if ($usuario['admin']): ?>
                                        <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                            <span class="badge bg-danger">Administrador Master</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Administrador Regular</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Usuário Comum</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">Licenças</h6>
                                    <hr>
                                    <?php if (isset($licencasInfo)): ?>
                                        <p class="mb-2"><strong>Total de Licenças:</strong> <?= $licencasInfo['total'] ?></p>
                                        <p class="mb-2"><strong>Licenças Utilizadas:</strong> <?= $licencasInfo['utilizadas'] ?></p>
                                        <p class="mb-0">
                                            <strong>Licenças Disponíveis:</strong>
                                            <?php if ($licencasInfo['disponiveis'] > 0): ?>
                                                <span class="badge bg-success"><?= $licencasInfo['disponiveis'] ?> disponíveis</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Nenhuma disponível</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted">Informações de licenças não disponíveis.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
                        <div class="alert alert-danger">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger me-3"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Não há licenças disponíveis!</h5>
                                    <p class="mb-0">Não é possível restaurar este usuário porque não há licenças disponíveis.</p>
                                    <?php if (is_admin_master()): ?>
                                        <hr>
                                        <div class="text-end">
                                            <a href="<?= base_url('licencas/criar') ?>" class="btn btn-primary">
                                                <i class="fas fa-plus-circle me-1"></i> Criar Nova Licença
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <p class="mb-0">Por favor, solicite ao administrador master que crie novas licenças.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <a href="<?= base_url('usuarios/restaurar/' . $usuario['id']) ?>" class="btn btn-success">
                                <i class="fas fa-user-check me-1"></i> Confirmar Restauração
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>