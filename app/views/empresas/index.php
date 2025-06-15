<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Empresas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('empresas/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nova Empresa
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($empresas) && !empty($empresas)): ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td><?= $empresa['id'] ?></td>
                                <td><?= $empresa['nome'] ?></td>
                                <td><?= $empresa['cnpj'] ?></td>
                                <td><?= $empresa['email'] ?></td>
                                <td><?= $empresa['telefone'] ?? '-' ?></td>
                                <td>
                                    <?php if ($empresa['ativo']): ?>
                                        <span class="badge bg-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('empresas/editar/' . $empresa['id']) ?>" class="btn btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('empresas/toggle/' . $empresa['id']) ?>" class="btn <?= $empresa['ativo'] ? 'btn-warning' : 'btn-success' ?>" title="<?= $empresa['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="fas <?= $empresa['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </a>
                                        <a href="<?= base_url('empresas/confirmarExclusao/' . $empresa['id']) ?>" class="btn btn-danger" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma empresa encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>