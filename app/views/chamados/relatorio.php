<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Chamados</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
            <a href="<?= base_url('chamados/listar') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Listar Chamados
            </a>
        </div>
        <a href="<?= base_url('chamados/exportar') . (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-file-export me-1"></i> Exportar Dados
        </a>
    </div>
</div>

<!-- Filtros do Relatório -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros do Relatório</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('chamados/relatorio') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="periodo" class="form-label">Período</label>
                <select class="form-select" id="periodo" name="periodo">
                    <option value="mes" <?= $filtros['periodo'] == 'mes' ? 'selected' : '' ?>>Mês Atual</option>
                    <option value="semana" <?= $filtros['periodo'] == 'semana' ? 'selected' : '' ?>>Semana Atual</option>
                    <option value="trimestre" <?= $filtros['periodo'] == 'trimestre' ? 'selected' : '' ?>>Trimestre Atual</option>
                    <option value="ano" <?= $filtros['periodo'] == 'ano' ? 'selected' : '' ?>>Ano Atual</option>
                    <option value="personalizado" <?= $filtros['periodo'] == 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
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
            <div class="col-md-3 periodo-personalizado" style="display: <?= $filtros['periodo'] == 'personalizado' ? 'block' : 'none' ?>;">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= $filtros['data_inicio'] ?>">
            </div>
            <div class="col-md-3 periodo-personalizado" style="display: <?= $filtros['periodo'] == 'personalizado' ? 'block' : 'none' ?>;">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= $filtros['data_fim'] ?>">
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Gerar Relatório</button>
            </div>
        </form>
    </div>
</div>

<!-- Resumo do Relatório -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Resumo do Relatório</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-primary">
                    <h6 class="alert-heading">Período do Relatório</h6>
                    <p class="mb-0">
                        <?php
                        if ($filtros['periodo'] == 'personalizado') {
                            echo 'De ' . date('d/m/Y', strtotime($filtros['data_inicio'])) . ' até ' . date('d/m/Y', strtotime($filtros['data_fim']));
                        } elseif ($filtros['periodo'] == 'mes') {
                            echo 'Mês atual: ' . date('m/Y');
                        } elseif ($filtros['periodo'] == 'semana') {
                            echo 'Semana atual: ' . date('d/m/Y', strtotime('monday this week')) . ' até ' . date('d/m/Y', strtotime('sunday this week'));
                        } elseif ($filtros['periodo'] == 'trimestre') {
                            $mes = date('m');
                            $trimestre = ceil($mes / 3);
                            echo 'Trimestre atual: ' . $trimestre . 'º trimestre de ' . date('Y');
                        } elseif ($filtros['periodo'] == 'ano') {
                            echo 'Ano atual: ' . date('Y');
                        }
                        ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Setor</h6>
                    <p class="mb-0">
                        <?php
                        if (!empty($filtros['setor'])) {
                            $setorEncontrado = false;
                            foreach ($setores as $setorItem) {
                                if ($setorItem['id'] == $filtros['setor']) {
                                    echo htmlspecialchars($setorItem['nome']);
                                    $setorEncontrado = true;
                                    break;
                                }
                            }
                            if (!$setorEncontrado) {
                                echo 'Setor não encontrado';
                            }
                        } else {
                            echo 'Todos os setores';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h2 class="display-4"><?= $dadosRelatorio['total_chamados'] ?></h2>
                        <p class="mb-0">Total de Chamados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h2 class="display-4">
                            <?php
                            $tempoMedio = $dadosRelatorio['tempo_atendimento']['tempo_medio'] ?? 0;
                            echo $tempoMedio > 0 ? formatarTempo(round($tempoMedio)) : 'N/A';
                            ?>
                        </h2>
                        <p class="mb-0">Tempo Médio de Atendimento</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h2 class="display-4">
                            <?php
                            $tempoMinimo = $dadosRelatorio['tempo_atendimento']['tempo_minimo'] ?? 0;
                            echo $tempoMinimo > 0 ? formatarTempo(round($tempoMinimo)) : 'N/A';
                            ?>
                        </h2>
                        <p class="mb-0">Tempo Mínimo de Atendimento</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h2 class="display-4">
                            <?php
                            $tempoMaximo = $dadosRelatorio['tempo_atendimento']['tempo_maximo'] ?? 0;
                            echo $tempoMaximo > 0 ? formatarTempo(round($tempoMaximo)) : 'N/A';
                            ?>
                        </h2>
                        <p class="mb-0">Tempo Máximo de Atendimento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos do Relatório -->
<div class="row mb-4">
    <!-- Chamados por Status -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Status</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($dadosRelatorio['chamados_por_status'])): ?>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">Percentual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dadosRelatorio['chamados_por_status'] as $status): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($status['nome']) ?></td>
                                        <td class="text-end"><?= $status['total'] ?></td>
                                        <td class="text-end"><?= number_format(($status['total'] / $dadosRelatorio['total_chamados']) * 100, 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chamados por Setor -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Setor</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($dadosRelatorio['chamados_por_setor'])): ?>
                    <div class="chart-container">
                        <canvas id="setorChart"></canvas>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Setor</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">Percentual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dadosRelatorio['chamados_por_setor'] as $setor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($setor['nome']) ?></td>
                                        <td class="text-end"><?= $setor['total'] ?></td>
                                        <td class="text-end"><?= number_format(($setor['total'] / $dadosRelatorio['total_chamados']) * 100, 1) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Chamados por Dia da Semana -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Dia da Semana</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($dadosRelatorio['chamados_por_dia_semana'])): ?>
                    <div class="chart-container">
                        <canvas id="diaSemanaChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chamados por Hora do Dia -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Hora do Dia</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($dadosRelatorio['chamados_por_hora'])): ?>
                    <div class="chart-container">
                        <canvas id="horaChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Controle do período personalizado
        const periodoSelect = document.getElementById('periodo');
        const camposPeriodoPersonalizado = document.querySelectorAll('.periodo-personalizado');

        periodoSelect.addEventListener('change', function() {
            if (this.value === 'personalizado') {
                camposPeriodoPersonalizado.forEach(campo => {
                    campo.style.display = 'block';
                });
            } else {
                camposPeriodoPersonalizado.forEach(campo => {
                    campo.style.display = 'none';
                });
            }
        });

        // Gráfico de Status
        <?php if (!empty($dadosRelatorio['chamados_por_status'])): ?>
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php foreach ($dadosRelatorio['chamados_por_status'] as $status): ?> '<?= htmlspecialchars($status['nome']) ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($dadosRelatorio['chamados_por_status'] as $status): ?>
                                <?= $status['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
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
        <?php endif; ?>

        // Gráfico de Setor
        <?php if (!empty($dadosRelatorio['chamados_por_setor'])): ?>
            const setorCtx = document.getElementById('setorChart').getContext('2d');
            const setorChart = new Chart(setorCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php foreach ($dadosRelatorio['chamados_por_setor'] as $setor): ?> '<?= htmlspecialchars($setor['nome']) ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($dadosRelatorio['chamados_por_setor'] as $setor): ?>
                                <?= $setor['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(201, 203, 207, 0.7)'
                        ],
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
        <?php endif; ?>

        // Gráfico de Dia da Semana
        <?php if (!empty($dadosRelatorio['chamados_por_dia_semana'])): ?>
            const diaSemanaCtx = document.getElementById('diaSemanaChart').getContext('2d');
            const diaSemanaChart = new Chart(diaSemanaCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($dadosRelatorio['chamados_por_dia_semana'] as $dia): ?> '<?= $dia['nome'] ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Chamados',
                        data: [
                            <?php foreach ($dadosRelatorio['chamados_por_dia_semana'] as $dia): ?>
                                <?= $dia['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
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
        <?php endif; ?>

        // Gráfico de Hora do Dia
        <?php if (!empty($dadosRelatorio['chamados_por_hora'])): ?>
            const horaCtx = document.getElementById('horaChart').getContext('2d');
            const horaChart = new Chart(horaCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php foreach ($dadosRelatorio['chamados_por_hora'] as $hora): ?> '<?= $hora['nome'] ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Chamados',
                        data: [
                            <?php foreach ($dadosRelatorio['chamados_por_hora'] as $hora): ?>
                                <?= $hora['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
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
        <?php endif; ?>
    });
</script>

<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>