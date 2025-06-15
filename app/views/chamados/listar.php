<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chamados</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
            <a href="<?= base_url('chamados/exportar') . (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-export me-1"></i> Exportar
            </a>
        </div>
        <a href="<?= base_url('chamados/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Chamado
        </a>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Filtros Avançados</h5>
        <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse" aria-expanded="false" aria-controls="filtrosCollapse">
            <i class="fas fa-filter"></i> Mostrar/Ocultar
        </button>
    </div>
    <div class="collapse <?= !empty(array_filter($filtros)) ? 'show' : '' ?>" id="filtrosCollapse">
        <div class="card-body">
            <form action="<?= base_url('chamados/listar') ?>" method="get" class="row g-3">
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
                <div class="col-md-3">
                    <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                    <select class="form-select" id="tipo_servico" name="tipo_servico">
                        <option value="">Todos</option>
                        <?php foreach ($tiposServico as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= $filtros['tipo_servico'] == $tipo ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="solicitante" class="form-label">Solicitante</label>
                    <select class="form-select" id="solicitante" name="solicitante">
                        <option value="">Todos</option>
                        <?php foreach ($solicitantes as $solicitante): ?>
                            <option value="<?= $solicitante ?>" <?= $filtros['solicitante'] == $solicitante ? 'selected' : '' ?>>
                                <?= htmlspecialchars($solicitante) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= $filtros['data_inicio'] ?>">
                </div>
                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= $filtros['data_fim'] ?>">
                </div>
                <div class="col-md-3">
                    <label for="ordenacao" class="form-label">Ordenação</label>
                    <select class="form-select" id="ordenacao" name="ordenacao">
                        <option value="recentes" <?= $filtros['ordenacao'] == 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                        <option value="antigos" <?= $filtros['ordenacao'] == 'antigos' ? 'selected' : '' ?>>Mais antigos</option>
                        <option value="status" <?= $filtros['ordenacao'] == 'status' ? 'selected' : '' ?>>Por status</option>
                        <option value="setor" <?= $filtros['ordenacao'] == 'setor' ? 'selected' : '' ?>>Por setor</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="busca" class="form-label">Busca</label>
                    <input type="text" class="form-control" id="busca" name="busca" value="<?= htmlspecialchars($filtros['busca']) ?>" placeholder="Descrição, solicitante ou paciente">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="<?= base_url('chamados/listar') ?>" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lista de Chamados -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Lista de Chamados</h5>
        <span class="badge bg-primary"><?= count($chamados) ?> chamados encontrados</span>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($chamados)): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Solicitante</th>
                            <th>Setor</th>
                            <th>Status</th>
                            <th>Tipo de Serviço</th>
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
                                            echo '<span class="badge bg-' . getStatusColor(strtolower(str_replace(' ', '_', $statusItem['nome']))) . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                            $statusEncontrado = true;
                                            break;
                                        }
                                    }
                                    if (!$statusEncontrado) {
                                        echo '<span class="badge bg-secondary">Desconhecido</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($chamado['tipo_servico'] ?? 'N/A') ?></td>
                                <td><?= formatarData($chamado['data_solicitacao']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-primary" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info m-3">
                Nenhum chamado encontrado com os filtros selecionados.
            </div>
        <?php endif; ?>
    </div>
</div>