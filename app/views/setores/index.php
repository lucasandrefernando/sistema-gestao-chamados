<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Setores</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm <?= !isset($mostrarRemovidos) || !$mostrarRemovidos ? 'btn-primary' : 'btn-outline-secondary' ?>" onclick="window.location.href='<?= base_url('setores') ?>'">
                <i class="fas fa-check-circle me-1"></i> Ativos
            </button>
            <button type="button" class="btn btn-sm <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'btn-danger' : 'btn-outline-secondary' ?>" onclick="window.location.href='<?= base_url('setores?mostrar_removidos=1') ?>'">
                <i class="fas fa-trash me-1"></i> Removidos
            </button>
        </div>
        <a href="<?= base_url('setores/criar') ?>" class="btn btn-sm btn-success">
            <i class="fas fa-plus me-1"></i> Novo Setor
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
                <h5 class="alert-heading mb-1">Visualizando setores removidos</h5>
                <p class="mb-0">Estes setores foram removidos do sistema, mas seus dados ainda estão armazenados. Você pode restaurá-los se necessário.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Setores Removidos' : 'Setores Ativos' ?>
            </h5>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 30%;">Nome</th>
                        <th style="width: 40%;">Descrição</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Chamados</th>
                        <th style="width: 100px;" class="text-center">Usuários</th>
                        <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                            <th style="width: 150px;" class="text-center">Removido em</th>
                        <?php endif; ?>
                        <th style="width: 150px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($setores) && !empty($setores)): ?>
                        <?php foreach ($setores as $setor): ?>
                            <tr <?= isset($setor['removido']) && $setor['removido'] ? 'class="table-danger bg-opacity-50"' : '' ?>>
                                <td class="text-center"><?= $setor['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                        </div>
                                        <?= $setor['nome'] ?>
                                    </div>
                                </td>
                                <td><?= $setor['descricao'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center">
                                    <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                        <span class="badge rounded-pill bg-danger px-3 py-2">Removido</span>
                                    <?php elseif ($setor['ativo']): ?>
                                        <span class="badge rounded-pill bg-success px-3 py-2">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-warning px-3 py-2">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill"><?= $setor['total_chamados'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill"><?= $setor['total_usuarios'] ?></span>
                                </td>
                                <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                                    <td class="text-center">
                                        <?php if (isset($setor['removido']) && $setor['removido'] && isset($setor['data_remocao'])): ?>
                                            <span class="text-danger">
                                                <i class="far fa-calendar-times me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($setor['data_remocao'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                            <button type="button" class="btn btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#restaurarModal"
                                                data-id="<?= $setor['id'] ?>"
                                                data-nome="<?= $setor['nome'] ?>"
                                                data-data="<?= date('d/m/Y H:i', strtotime($setor['data_remocao'])) ?>">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="btn btn-info" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="btn <?= $setor['ativo'] ? 'btn-warning' : 'btn-success' ?>" title="<?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $setor['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                            </a>
                                            <?php if ($setor['total_chamados'] == 0 && $setor['total_usuarios'] == 0): ?>
                                                <button type="button" class="btn btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#removerModal"
                                                    data-id="<?= $setor['id'] ?>"
                                                    data-nome="<?= $setor['nome'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-danger" disabled title="Não é possível remover este setor pois existem chamados ou usuários associados a ele">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= isset($mostrarRemovidos) && $mostrarRemovidos ? '8' : '7' ?>" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="mb-0">Nenhum setor <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
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
                    <h5>Tem certeza que deseja remover este setor?</h5>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    O setor será marcado como removido, mas seus dados permanecerão no sistema. Você poderá restaurá-lo posteriormente se necessário.
                </div>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <p class="mb-0"><strong>Nome:</strong> <span id="removerNome"></span></p>
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
                    <i class="fas fa-trash-restore text-success fa-4x mb-3"></i>
                    <h5>Tem certeza que deseja restaurar este setor?</h5>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    O setor será restaurado e poderá ser utilizado novamente.
                </div>
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <p class="mb-1"><strong>Nome:</strong> <span id="restaurarNome"></span></p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Modal de remoção
        var removerModal = document.getElementById('removerModal');
        if (removerModal) {
            removerModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');

                document.getElementById('removerNome').textContent = nome;
                document.getElementById('confirmarRemover').href = '<?= base_url('setores/remover/') ?>' + id;
            });
        }

        // Modal de restauração
        var restaurarModal = document.getElementById('restaurarModal');
        if (restaurarModal) {
            restaurarModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var data = button.getAttribute('data-data');

                document.getElementById('restaurarNome').textContent = nome;
                document.getElementById('restaurarData').textContent = data;
                document.getElementById('confirmarRestaurar').href = '<?= base_url('setores/restaurar/') ?>' + id;
            });
        }
    });
</script>