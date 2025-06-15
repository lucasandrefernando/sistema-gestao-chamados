<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Usuários do Setor: <?= $setor['nome'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('setores') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar para Setores
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Informações do Setor</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nome:</strong> <?= $setor['nome'] ?></p>
                <p><strong>Empresa:</strong> <?= $_SESSION['empresa_nome'] ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong>
                    <?php if ($setor['ativo']): ?>
                        <span class="badge bg-success">Ativo</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Inativo</span>
                    <?php endif; ?>
                </p>
                <p><strong>Descrição:</strong> <?= $setor['descricao'] ?? '<span class="text-muted">-</span>' ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Usuários Associados</h5>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#associarUsuarioModal">
                <i class="fas fa-user-plus me-1"></i> Associar Usuário
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 30%;">Nome</th>
                        <th style="width: 30%;">E-mail</th>
                        <th style="width: 20%;">Cargo</th>
                        <th style="width: 100px;" class="text-center">Principal</th>
                        <th style="width: 150px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($usuariosSetor) && !empty($usuariosSetor)): ?>
                        <?php foreach ($usuariosSetor as $usuarioSetor): ?>
                            <tr>
                                <td class="text-center"><?= $usuarioSetor['usuario_id'] ?></td>
                                <td><?= $usuarioSetor['nome'] ?></td>
                                <td><?= $usuarioSetor['email'] ?></td>
                                <td><?= $usuarioSetor['cargo'] ?? '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center">
                                    <?php if ($usuarioSetor['principal']): ?>
                                        <span class="badge bg-success">Sim</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editarAssociacaoModal"
                                            data-usuario-id="<?= $usuarioSetor['usuario_id'] ?>"
                                            data-usuario-nome="<?= $usuarioSetor['nome'] ?>"
                                            data-principal="<?= $usuarioSetor['principal'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#desassociarUsuarioModal"
                                            data-usuario-id="<?= $usuarioSetor['usuario_id'] ?>"
                                            data-usuario-nome="<?= $usuarioSetor['nome'] ?>">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p class="mb-0">Nenhum usuário associado a este setor.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Associar Usuário -->
<div class="modal fade" id="associarUsuarioModal" tabindex="-1" aria-labelledby="associarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="associarUsuarioModalLabel">Associar Usuário ao Setor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 30%;">Nome</th>
                                <th style="width: 30%;">E-mail</th>
                                <th style="width: 20%;">Cargo</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th style="width: 100px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($usuarios) && !empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td class="text-center"><?= $usuario['id'] ?></td>
                                        <td><?= $usuario['nome'] ?></td>
                                        <td><?= $usuario['email'] ?></td>
                                        <td><?= $usuario['cargo'] ?? '<span class="text-muted">-</span>' ?></td>
                                        <td class="text-center">
                                            <?php if ($usuario['associado']): ?>
                                                <span class="badge bg-success">Associado</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Não Associado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($usuario['associado']): ?>
                                                <form action="<?= base_url('setores/associarUsuario') ?>" method="post">
                                                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="associar" value="0">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-unlink"></i> Desassociar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= base_url('setores/associarUsuario') ?>" method="post">
                                                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="associar" value="1">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input" type="checkbox" name="principal" id="principal_<?= $usuario['id'] ?>" value="1">
                                                            <label class="form-check-label" for="principal_<?= $usuario['id'] ?>">
                                                                Principal
                                                            </label>
                                                        </div>
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-link"></i> Associar
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p class="mb-0">Nenhum usuário disponível para associação.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Associação -->
<div class="modal fade" id="editarAssociacaoModal" tabindex="-1" aria-labelledby="editarAssociacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editarAssociacaoModalLabel">Editar Associação</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('setores/associarUsuario') ?>" method="post" id="formEditarAssociacao">
                <div class="modal-body">
                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                    <input type="hidden" name="usuario_id" id="editarUsuarioId">
                    <input type="hidden" name="associar" value="1">

                    <p>Editando associação do usuário <strong id="editarUsuarioNome"></strong> com o setor <strong><?= $setor['nome'] ?></strong>.</p>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="principal" id="editarPrincipal" value="1">
                        <label class="form-check-label" for="editarPrincipal">
                            Usuário Principal do Setor
                        </label>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1 text-info"></i> Usuários principais são os responsáveis pelo setor.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Desassociar Usuário -->
<div class="modal fade" id="desassociarUsuarioModal" tabindex="-1" aria-labelledby="desassociarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="desassociarUsuarioModalLabel">Confirmar Desassociação</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('setores/associarUsuario') ?>" method="post" id="formDesassociarUsuario">
                <div class="modal-body">
                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                    <input type="hidden" name="usuario_id" id="desassociarUsuarioId">
                    <input type="hidden" name="associar" value="0">

                    <div class="text-center mb-4">
                        <i class="fas fa-unlink text-danger fa-4x mb-3"></i>
                        <h5>Tem certeza que deseja desassociar este usuário do setor?</h5>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        O usuário <strong id="desassociarUsuarioNome"></strong> não terá mais acesso aos recursos deste setor.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Desassociação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal para editar associação
        var editarAssociacaoModal = document.getElementById('editarAssociacaoModal');
        if (editarAssociacaoModal) {
            editarAssociacaoModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var usuarioId = button.getAttribute('data-usuario-id');
                var usuarioNome = button.getAttribute('data-usuario-nome');
                var principal = button.getAttribute('data-principal') === '1';

                document.getElementById('editarUsuarioId').value = usuarioId;
                document.getElementById('editarUsuarioNome').textContent = usuarioNome;
                document.getElementById('editarPrincipal').checked = principal;
            });
        }

        // Modal para desassociar usuário
        var desassociarUsuarioModal = document.getElementById('desassociarUsuarioModal');
        if (desassociarUsuarioModal) {
            desassociarUsuarioModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var usuarioId = button.getAttribute('data-usuario-id');
                var usuarioNome = button.getAttribute('data-usuario-nome');

                document.getElementById('desassociarUsuarioId').value = usuarioId;
                document.getElementById('desassociarUsuarioNome').textContent = usuarioNome;
            });
        }
    });
</script>