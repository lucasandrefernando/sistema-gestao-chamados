<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciar Setores</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('setores/admin?mostrar_removidos=' . ($mostrarRemovidos ? '0' : '1')) ?>" class="btn btn-sm btn-outline-secondary">
                <?= $mostrarRemovidos ? 'Ocultar Removidos' : 'Mostrar Removidos' ?>
            </a>
        </div>
        <a href="<?= base_url('setores/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Setor
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Chamados</th>
                        <th>Usuários</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($setores as $setor): ?>
                        <tr class="<?= isset($setor['removido']) && $setor['removido'] ? 'table-danger' : '' ?>">
                            <td><?= $setor['id'] ?></td>
                            <td><?= htmlspecialchars($setor['nome']) ?></td>
                            <td>
                                <?php if (!empty($setor['descricao'])): ?>
                                    <?= htmlspecialchars(substr($setor['descricao'], 0, 50)) ?>
                                    <?= strlen($setor['descricao']) > 50 ? '...' : '' ?>
                                <?php else: ?>
                                    <span class="text-muted"><em>Sem descrição</em></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $setor['total_chamados'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $setor['total_usuarios'] ?></span>
                            </td>
                            <td>
                                <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                    <span class="badge bg-danger">Removido</span>
                                <?php else: ?>
                                    <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?>">
                                        <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                    <a href="<?= base_url('setores/restaurar/' . $setor['id']) ?>" class="btn btn-sm btn-success" title="Restaurar">
                                        <i class="fas fa-trash-restore"></i>
                                    </a>
                                <?php else: ?>
                                    <div class="btn-group">
                                        <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="btn btn-sm btn-info" title="Gerenciar Usuários">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="btn btn-sm btn-<?= $setor['ativo'] ? 'warning' : 'success' ?>" title="<?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="fas fa-<?= $setor['ativo'] ? 'times' : 'check' ?>"></i>
                                        </a>
                                        <?php if ($setor['total_chamados'] == 0 && $setor['total_usuarios'] == 0): ?>
                                            <a href="<?= base_url('setores/remover/' . $setor['id']) ?>" class="btn btn-sm btn-danger" title="Remover" onclick="return confirm('Tem certeza que deseja remover este setor?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($setores)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhum setor encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>