<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Licenças</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('licencas/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nova Licença
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
                        <th>Empresa</th>
                        <th>Quantidade</th>
                        <th>Data de Início</th>
                        <th>Data de Fim</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($licencas) && !empty($licencas)): ?>
                        <?php foreach ($licencas as $licenca): ?>
                            <?php
                            $hoje = date('Y-m-d');
                            $status = 'Inativa';
                            $statusClass = 'bg-danger';

                            if ($licenca['ativo']) {
                                if ($licenca['data_inicio'] <= $hoje && $licenca['data_fim'] >= $hoje) {
                                    $status = 'Ativa';
                                    $statusClass = 'bg-success';
                                } elseif ($licenca['data_inicio'] > $hoje) {
                                    $status = 'Futura';
                                    $statusClass = 'bg-info';
                                } elseif ($licenca['data_fim'] < $hoje) {
                                    $status = 'Expirada';
                                    $statusClass = 'bg-warning';
                                }
                            }
                            ?>
                            <tr>
                                <td><?= $licenca['id'] ?></td>
                                <td><?= $empresas[$licenca['empresa_id']]['nome'] ?? 'Empresa não encontrada' ?></td>
                                <td><?= $licenca['quantidade'] ?></td>
                                <td><?= date('d/m/Y', strtotime($licenca['data_inicio'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($licenca['data_fim'])) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('licencas/editar/' . $licenca['id']) ?>" class="btn btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('licencas/toggle/' . $licenca['id']) ?>" class="btn <?= $licenca['ativo'] ? 'btn-warning' : 'btn-success' ?>" title="<?= $licenca['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="fas <?= $licenca['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma licença encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>