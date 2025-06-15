<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Usuários</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm <?= !isset($mostrarRemovidos) || !$mostrarRemovidos ? 'btn-primary' : 'btn-outline-secondary' ?>" onclick="window.location.href='<?= base_url('usuarios') ?>'">
                <i class="fas fa-user-check me-1"></i> Ativos
            </button>
            <button type="button" class="btn btn-sm <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'btn-danger' : 'btn-outline-secondary' ?>" onclick="window.location.href='<?= base_url('usuarios?mostrar_removidos=1') ?>'">
                <i class="fas fa-user-slash me-1"></i> Removidos
            </button>
        </div>
        <a href="<?= base_url('usuarios/criar') ?>" class="btn btn-sm btn-success">
            <i class="fas fa-user-plus me-1"></i> Novo Usuário
        </a>
    </div>
</div>

<?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
    <div class="alert alert-info bg-light border-info">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle fa-2x text-info me-3"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">Visualizando usuários removidos</h5>
                <p class="mb-0">Estes usuários foram removidos do sistema, mas seus dados ainda estão armazenados. Você pode restaurá-los se necessário.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['usuario_removido_encontrado']) && $_SESSION['usuario_removido_encontrado']): ?>
    <div class="alert alert-warning">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Usuário removido encontrado!</h5>
                <p>Detectamos que você está tentando criar um usuário com um e-mail que pertence a um usuário removido.</p>
                <hr>
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-0"><strong>Nome:</strong> <?= $_SESSION['usuario_removido_nome'] ?><br>
                            <strong>E-mail:</strong> <?= $_SESSION['usuario_removido_email'] ?><br>
                            <strong>Removido em:</strong> <?= date('d/m/Y H:i', strtotime($_SESSION['usuario_removido_data'])) ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?= base_url('usuarios/confirmarRestauracao/' . $_SESSION['usuario_removido_id']) ?>" class="btn btn-success">
                            <i class="fas fa-user-check me-1"></i> Restaurar Usuário
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Limpa as variáveis de sessão após exibir o alerta
    unset($_SESSION['usuario_removido_encontrado']);
    unset($_SESSION['usuario_removido_id']);
    unset($_SESSION['usuario_removido_nome']);
    unset($_SESSION['usuario_removido_email']);
    unset($_SESSION['usuario_removido_data']);
    ?>
<?php endif; ?>

<?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
    <div class="alert alert-warning">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading">Limite de licenças atingido!</h5>
                <p>Não há licenças disponíveis para criar novos usuários.</p>
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
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Usuários Removidos' : 'Usuários Ativos' ?>
            </h5>
            <?php if (isset($licencasInfo)): ?>
                <div class="badge bg-info text-white p-2">
                    <i class="fas fa-users me-1"></i> <?= $licencasInfo['utilizadas'] ?> / <?= $licencasInfo['total'] ?> licenças utilizadas
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($licencasInfo)): ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-primary">Total de Licenças</h6>
                                    <small class="text-muted">Licenças ativas</small>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-key fa-2x text-primary"></i>
                                </div>
                            </div>
                            <h3 class="mt-3 mb-0"><?= $licencasInfo['total'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-success">Licenças Utilizadas</h6>
                                    <small class="text-muted">Usuários ativos</small>
                                </div>
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-user-check fa-2x text-success"></i>
                                </div>
                            </div>
                            <h3 class="mt-3 mb-0"><?= $licencasInfo['utilizadas'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 <?= $licencasInfo['disponiveis'] > 0 ? 'text-info' : 'text-danger' ?>">Licenças Disponíveis</h6>
                                    <small class="text-muted">Restantes</small>
                                </div>
                                <div class="<?= $licencasInfo['disponiveis'] > 0 ? 'bg-info' : 'bg-danger' ?> bg-opacity-10 rounded-circle p-3">
                                    <i class="fas <?= $licencasInfo['disponiveis'] > 0 ? 'fa-unlock' : 'fa-lock' ?> fa-2x <?= $licencasInfo['disponiveis'] > 0 ? 'text-info' : 'text-danger' ?>"></i>
                                </div>
                            </div>
                            <h3 class="mt-3 mb-0"><?= $licencasInfo['disponiveis'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 20%;">Nome</th>
                        <th style="width: 20%;">E-mail</th>
                        <th style="width: 15%;">Cargo</th>
                        <th style="width: 80px;" class="text-center">Admin</th>
                        <th style="width: 100px;" class="text-center">Tipo</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                            <th style="width: 150px;" class="text-center">Removido em</th>
                        <?php endif; ?>
                        <th style="width: 150px;" class="text-center">Último Acesso</th>
                        <th style="width: 150px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($usuarios) && !empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr <?= isset($usuario['removido']) && $usuario['removido'] ? 'class="table-danger bg-opacity-50"' : '' ?>>
                                <td class="text-center"><?= $usuario['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-<?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'danger' : 'primary') : 'secondary' ?> text-white me-2">
                                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                        </div>
                                        <?= $usuario['nome'] ?>
                                    </div>
                                </td>
                                <td><?= $usuario['email'] ?></td>
                                <td><?= $usuario['cargo'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center">
                                    <?php if ($usuario['admin']): ?>
                                        <span class="badge rounded-pill bg-primary px-3 py-2">Sim</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary px-3 py-2">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($usuario['admin']): ?>
                                        <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                            <span class="badge rounded-pill bg-danger px-3 py-2">Master</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-info px-3 py-2">Regular</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary px-3 py-2">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                        <span class="badge rounded-pill bg-danger px-3 py-2">Removido</span>
                                    <?php elseif ($usuario['ativo']): ?>
                                        <span class="badge rounded-pill bg-success px-3 py-2">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-warning px-3 py-2">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                                    <td class="text-center">
                                        <?php if (isset($usuario['removido']) && $usuario['removido'] && isset($usuario['data_remocao'])): ?>
                                            <span class="text-danger">
                                                <i class="far fa-calendar-times me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <?php if ($usuario['ultimo_acesso']): ?>
                                        <span data-bs-toggle="tooltip" title="<?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>">
                                            <i class="far fa-clock me-1"></i>
                                            <?= isset($usuario['tempo_decorrido']) ? $usuario['tempo_decorrido'] : date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                            <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                                <button type="button" class="btn btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#restaurarModal"
                                                    data-id="<?= $usuario['id'] ?>"
                                                    data-nome="<?= $usuario['nome'] ?>"
                                                    data-email="<?= $usuario['email'] ?>"
                                                    data-data="<?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?= base_url('usuarios/editar/' . $usuario['id']) ?>" class="btn btn-info" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($usuario['id'] != get_user_id()): ?>
                                                <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                                    <a href="<?= base_url('usuarios/toggle/' . $usuario['id']) ?>" class="btn <?= $usuario['ativo'] ? 'btn-warning' : 'btn-success' ?>" title="<?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                        <i class="fas <?= $usuario['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#removerModal"
                                                        data-id="<?= $usuario['id'] ?>"
                                                        data-nome="<?= $usuario['nome'] ?>"
                                                        data-email="<?= $usuario['email'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= isset($mostrarRemovidos) && $mostrarRemovidos ? '10' : '9' ?>" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="mb-0">Nenhum usuário <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Remoção -->
<div class="modal fade" id="removerModal" tabindex="-1" aria-labelledby="removerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="removerModalLabel">Confirmar Remoção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                    <h5>Tem certeza que deseja remover este usuário?</h5>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    O usuário será marcado como removido, mas seus dados permanecerão no sistema. Você poderá restaurá-lo posteriormente se necessário.
                </div>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <p class="mb-1"><strong>Nome:</strong> <span id="removerNome"></span></p>
                        <p class="mb-0"><strong>E-mail:</strong> <span id="removerEmail"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRemover" class="btn btn-danger">Confirmar Remoção</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Restauração -->
<div class="modal fade" id="restaurarModal" tabindex="-1" aria-labelledby="restaurarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restaurarModalLabel">Confirmar Restauração</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-check text-success fa-4x mb-3"></i>
                    <h5>Tem certeza que deseja restaurar este usuário?</h5>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    O usuário será restaurado e poderá acessar o sistema novamente.
                </div>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <p class="mb-1"><strong>Nome:</strong> <span id="restaurarNome"></span></p>
                        <p class="mb-1"><strong>E-mail:</strong> <span id="restaurarEmail"></span></p>
                        <p class="mb-0"><strong>Removido em:</strong> <span id="restaurarData"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRestaurar" class="btn btn-success">Confirmar Restauração</a>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>

<script>
    // Toggle para mostrar/ocultar usuários removidos
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Modal de remoção
        var removerModal = document.getElementById('removerModal');
        if (removerModal) {
            removerModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');

                document.getElementById('removerNome').textContent = nome;
                document.getElementById('removerEmail').textContent = email;
                document.getElementById('confirmarRemover').href = '<?= base_url('usuarios/remover/') ?>' + id;
            });
        }

        // Modal de restauração
        var restaurarModal = document.getElementById('restaurarModal');
        if (restaurarModal) {
            restaurarModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');
                var data = button.getAttribute('data-data');

                document.getElementById('restaurarNome').textContent = nome;
                document.getElementById('restaurarEmail').textContent = email;
                document.getElementById('restaurarData').textContent = data;
                document.getElementById('confirmarRestaurar').href = '<?= base_url('usuarios/restaurar/') ?>' + id;
            });
        }
    });
</script>