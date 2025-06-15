<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard de Chamados</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados/listar') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Listar Chamados
            </a>
            <a href="<?= base_url('chamados/relatorio') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-chart-bar me-1"></i> Relatórios
            </a>
        </div>
        <a href="<?= base_url('chamados/criar') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Chamado
        </a>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total de Chamados</h6>
                        <h2 class="mb-0"><?= $estatisticas['total'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-ticket-alt fa-3x opacity-50"></i>
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
        <div class="card text-white bg-danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Chamados Abertos</h6>
                        <h2 class="mb-0"><?= $estatisticas['abertos'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
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
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Em Atendimento</h6>
                        <h2 class="mb-0"><?= $estatisticas['em_andamento'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
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
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Concluídos</h6>
                        <h2 class="mb-0"><?= $estatisticas['concluidos'] ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
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
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Mês</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtro Rápido e Chamados Recentes -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtro Rápido</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get">
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
                                        <td><?= htmlspecialchars(substr($chamado['descricao'], 0, 30)) . (strlen($chamado['descricao']) > 30 ? '...' : '') ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chamadosPorStatus['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($chamadosPorStatus['data']) ?>,
                    backgroundColor: <?= json_encode($chamadosPorStatus['backgroundColor']) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Gráfico de Chamados por Mês
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chamadosPorMes['labels']) ?>,
                datasets: [{
                    label: 'Chamados',
                    data: <?= json_encode($chamadosPorMes['data']) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>