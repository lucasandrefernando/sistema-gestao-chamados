<!-- views/chamados/index.php -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chamados</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('chamados/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Chamado
        </a>
    </div>
</div>
 
<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('chamados') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <?php foreach ($statusList as $statusItem): ?>
                        <option value="<?= $statusItem['id'] ?>" <?= $filtros['status'] == $statusItem['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($statusItem['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="setor" class="form-label">Setor</label>
                <select class="form-select" id="setor" name="setor">
                    <option value="">Todos</option>
                    <?php foreach ($setores as $setorItem): ?>
                        <option value="<?= $setorItem['id'] ?>" <?= $filtros['setor'] == $setorItem['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($setorItem['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

  
            <div class="col-md-4">
                <label for="busca" class="form-label">Busca</label>
                <input type="text" class="form-control" id="busca" name="busca" value="<?= htmlspecialchars($filtros['busca']) ?>"
                    placeholder="Descrição ou solicitante">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                <a href="<?= base_url('chamados') ?>" class="btn btn-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Chamados -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Chamados</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($chamados)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Solicitante</th>
                            <th>Setor</th>
                            <th>Status</th>
                            <th>Data de Solicitação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chamados as $chamado): ?>
                            <tr>
                                <td><?= $chamado['id'] ?></td>
                                <td><?= htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') ?></td>
                                <td><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                <td>
                                    <?php
                                    $setorEncontrado = false;
                                    foreach ($setores as $setorItem) {
                                        if ($setorItem['id'] == $chamado['setor_id']) {
                                            echo htmlspecialchars($setorItem['nome']);
                                            $setorEncontrado = true;
                                            break;
                                        }
                                    }
                                    if (!$setorEncontrado) {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $statusEncontrado = false;
                                    foreach ($statusList as $statusItem) {
                                        if ($statusItem['id'] == $chamado['status_id']) {
                                            echo '<span class="badge bg-' . getStatusColor($statusItem['nome']) . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                            $statusEncontrado = true;
                                            break;
                                        }
                                    }
                                    if (!$statusEncontrado) {
                                        echo '<span class="badge bg-secondary">Desconhecido</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= formatarData($chamado['data_solicitacao']) ?></td>
                                <td>
                                    <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-primary" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Nenhum chamado encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>