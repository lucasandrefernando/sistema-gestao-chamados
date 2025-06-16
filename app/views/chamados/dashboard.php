<div class="page-header">
    <h1 class="page-title">Dashboard de Chamados</h1>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados/listar') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Listar Chamados
            </a>
            <a href="<?= base_url('chamados/relatorio') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-chart-bar me-1"></i> Relatórios
            </a>
        </div>
        <a href="<?= base_url('chamados/criar') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Chamado
        </a>
    </div>
</div>

<!-- Elemento oculto para armazenar dados dos gráficos -->
<div id="dashboard-data"
    data-status='<?= json_encode($chamadosPorStatus) ?>'
    data-monthly='<?= json_encode($chamadosPorMes) ?>'
    data-ano='<?= $anoFiltro ?? date('Y') ?>'>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total de Chamados</h6>
                        <h2 class="mb-0"><?= $estatisticas['total'] ?? 0 ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?= base_url('chamados/listar') ?>" class="text-white text-decoration-none small">Ver todos</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Chamados Abertos</h6>
                        <h2 class="mb-0"><?= $estatisticas['abertos'] ?? 0 ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?= base_url('chamados/listar?status=1') ?>" class="text-white text-decoration-none small">Ver detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Em Atendimento</h6>
                        <h2 class="mb-0"><?= $estatisticas['em_andamento'] ?? 0 ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?= base_url('chamados/listar?status=2') ?>" class="text-white text-decoration-none small">Ver detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Concluídos</h6>
                        <h2 class="mb-0"><?= $estatisticas['concluidos'] ?? 0 ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="<?= base_url('chamados/listar?status=4') ?>" class="text-white text-decoration-none small">Ver detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Gráfico de Chamados por Status -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Status</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Chamados por Mês -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Chamados por Mês</h5>
                <div class="year-selector">
                    <form id="formAnoFiltro" method="get" action="<?= base_url('chamados') ?>" class="d-flex align-items-center">
                        <label for="anoFiltro" class="me-2 mb-0">Ano:</label>
                        <select id="anoFiltro" name="ano" class="form-select form-select-sm" style="width: auto;">
                            <?php foreach ($anosDisponiveis ?? [date('Y')] as $ano): ?>
                                <option value="<?= $ano ?>" <?= ($anoFiltro ?? date('Y')) == $ano ? 'selected' : '' ?>>
                                    <?= $ano ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Filtro Rápido -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtro Rápido</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get" class="quick-filter-form">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusList as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>">
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="setor" class="form-label">Setor</label>
                        <select class="form-select" id="setor" name="setor">
                            <option value="">Todos</option>
                            <?php foreach ($setores as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>">
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="data_inicio" class="form-label">Data Inicial</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                    </div>
                    <div class="mb-3">
                        <label for="data_fim" class="form-label">Data Final</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Chamados Recentes -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Chamados Recentes</h5>
                <a href="<?= base_url('chamados/listar') ?>" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Solicitante</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($chamadosRecentes)): ?>
                                <?php foreach ($chamadosRecentes as $chamado): ?>
                                    <tr>
                                        <td><?= $chamado['id'] ?></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                                <?= htmlspecialchars(substr($chamado['descricao'], 0, 30)) . (strlen($chamado['descricao']) > 30 ? '...' : '') ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($chamado['solicitante']) ?></td>
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
                                        <td><?= formatarData($chamado['data_solicitacao']) ?></td>
                                        <td>
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">Nenhum chamado encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>