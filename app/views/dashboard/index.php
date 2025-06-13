<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="fas fa-calendar me-1"></i> Esta semana
        </button>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total de Chamados</h6>
                        <h2 class="mb-0"><?= $estatisticas['total'] ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-ticket-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Chamados Abertos</h6>
                        <h2 class="mb-0"><?= $estatisticas['abertos'] ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Concluídos Hoje</h6>
                        <h2 class="mb-0"><?= $estatisticas['concluidos_hoje'] ?? 0 ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Setor</h5>
            </div>
            <div class="card-body">
                <canvas id="chamadosPorSetorChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Status</h5>
            </div>
            <div class="card-body">
                <canvas id="chamadosPorStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chamados recentes -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Chamados Recentes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Solicitante</th>
                        <th>Paciente</th>
                        <th>Setor</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($estatisticas['recentes']) && !empty($estatisticas['recentes'])): ?>
                        <?php foreach ($estatisticas['recentes'] as $chamado): ?>
                            <tr>
                                <td><?= $chamado['id'] ?></td>
                                <td><?= $chamado['solicitante'] ?></td>
                                <td><?= $chamado['paciente'] ?></td>
                                <td><?= $chamado['setor'] ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= $chamado['status_cor'] ?>">
                                        <?= $chamado['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])) ?></td>
                                <td>
                                    <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhum chamado encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para os gráficos (serão substituídos por dados reais)
        const setoresData = {
            labels: ['Manutenção', 'Rouparia', 'Higienização', 'Nutrição', 'Religiosos', 'TI'],
            datasets: [{
                label: 'Chamados por Setor',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        };

        const statusData = {
            labels: ['Aberto', 'Em Atendimento', 'Pausado', 'Concluído', 'Cancelado'],
            datasets: [{
                label: 'Chamados por Status',
                data: [15, 8, 3, 25, 5],
                backgroundColor: [
                    'rgba(255, 0, 0, 0.7)',
                    'rgba(255, 165, 0, 0.7)',
                    'rgba(255, 255, 0, 0.7)',
                    'rgba(0, 255, 0, 0.7)',
                    'rgba(128, 128, 128, 0.7)'
                ],
                borderWidth: 1
            }]
        };

        // Configuração dos gráficos
        const setoresChart = new Chart(
            document.getElementById('chamadosPorSetorChart'), {
                type: 'bar',
                data: setoresData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    }
                }
            }
        );

        const statusChart = new Chart(
            document.getElementById('chamadosPorStatusChart'), {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    }
                }
            }
        );
    });
</script>