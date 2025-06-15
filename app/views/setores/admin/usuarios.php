<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciar Usuários do Setor: <?= htmlspecialchars($setor['nome']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('setores/admin') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row mb-4">
    <!-- Informações do Setor -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Setor</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($setor['descricao'])): ?>
                    <p class="card-text"><?= nl2br(htmlspecialchars($setor['descricao'])) ?></p>
                <?php else: ?>
                    <p class="card-text text-muted"><em>Sem descrição</em></p>
                <?php endif; ?>

                <hr>

                <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?>">
                        <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </div>

                <div class="mb-2">
                    <strong>Total de Usuários:</strong>
                    <span class="badge bg-primary"><?= count($usuariosSetor) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuários Associados -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Usuários Associados</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($usuariosSetor)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Cargo</th>
                                    <th>Principal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuariosSetor as $usuarioSetor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usuarioSetor['nome']) ?></td>
                                        <td><?= htmlspecialchars($usuarioSetor['email']) ?></td>
                                        <td><?= htmlspecialchars($usuarioSetor['cargo']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $usuarioSetor['principal'] ? 'success' : 'secondary' ?>">
                                                <?= $usuarioSetor['principal'] ? 'Sim' : 'Não' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="<?= base_url('setores/associarUsuario') ?>" method="post" class="d-inline">
                                                <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                <input type="hidden" name="usuario_id" value="<?= $usuarioSetor['usuario_id'] ?>">
                                                <input type="hidden" name="associar" value="0">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Desassociar" onclick="return confirm('Tem certeza que deseja desassociar este usuário do setor?')">
                                                    <i class="fas fa-unlink"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Nenhum usuário associado a este setor.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Associar Usuários -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Associar Usuários</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($usuarios)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Cargo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['cargo']) ?></td>
                                <td>
                                    <?php if ($usuario['associado']): ?>
                                        <span class="badge bg-success">Associado</span>
                                        <?php if ($usuario['principal']): ?>
                                            <span class="badge bg-info">Principal</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Não Associado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$usuario['associado']): ?>
                                        <form action="<?= base_url('setores/associarUsuario') ?>" method="post" class="d-inline">
                                            <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                            <input type="hidden" name="associar" value="1">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="principal_<?= $usuario['id'] ?>" name="principal" value="1">
                                                <label class="form-check-label" for="principal_<?= $usuario['id'] ?>">Principal</label>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-success" title="Associar">
                                                <i class="fas fa-link"></i> Associar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= base_url('setores/associarUsuario') ?>" method="post" class="d-inline">
                                            <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                            <input type="hidden" name="associar" value="1">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="principal_<?= $usuario['id'] ?>" name="principal" value="1" <?= $usuario['principal'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="principal_<?= $usuario['id'] ?>">Principal</label>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary" title="Atualizar">
                                                <i class="fas fa-sync"></i> Atualizar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Nenhum usuário disponível para associar.
            </div>
        <?php endif; ?>
    </div>
</div>