<div class="dashboard-container">
    <!-- Cabeçalho da Página com Design Aprimorado -->
    <div class="user-dashboard-header">
        <div class="header-content">
            <div class="title-section">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt title-icon"></i>
                    Painel de Controle
                </h1>
                <p class="subtitle">Visão geral e análise de desempenho do sistema de chamados</p>
            </div>
            <div class="action-buttons">
                <a href="<?= base_url('chamados/criar') ?>" class="btn-new-ticket">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas com Design Aprimorado -->
    <div class="stats-cards">
        <div class="stat-card total-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="total-chamados"><?= $estatisticas['total'] ?? 0 ?></div>
                <div class="stat-label">Total de Chamados</div>
                <div class="stat-description">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <div class="stat-card warning-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="chamados-abertos"><?= $estatisticas['abertos'] ?? 0 ?></div>
                <div class="stat-label">Chamados Abertos</div>
                <div class="stat-description">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <div class="stat-card info-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="chamados-andamento"><?= $estatisticas['em_andamento'] ?? 0 ?></div>
                <div class="stat-label">Em Atendimento</div>
                <div class="stat-description">Chamados que estão sendo processados</div>
            </div>
        </div>

        <div class="stat-card success-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="chamados-concluidos"><?= $estatisticas['concluidos'] ?? 0 ?></div>
                <div class="stat-label">Concluídos</div>
                <div class="stat-description">Chamados finalizados com sucesso</div>
            </div>
        </div>

        <div class="stat-card success-light-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="concluidos-hoje"><?= $estatisticas['concluidos_hoje'] ?? 0 ?></div>
                <div class="stat-label">Concluídos Hoje</div>
                <div class="stat-description">Chamados finalizados nas últimas 24h</div>
            </div>
        </div>

        <div class="stat-card primary-light-card">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="tempo-medio"><?= $estatisticas['tempo_medio_atendimento'] ?? 0 ?> h</div>
                <div class="stat-label">Tempo Médio</div>
                <div class="stat-description">Tempo médio de resolução de chamados</div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principais -->
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Chamados por Status</h5>
            </div>
            <div class="chart-body">
                <canvas id="chamadosPorStatusChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Chamados por Setor</h5>
            </div>
            <div class="chart-body">
                <canvas id="chamadosPorSetorChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos Secundários -->
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Chamados por Mês</h5>
            </div>
            <div class="chart-body">
                <canvas id="chamadosPorMesChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Tempo Médio por Setor (horas)</h5>
            </div>
            <div class="chart-body">
                <canvas id="tempoMedioPorSetorChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficos Terciários -->
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Chamados por Tipo de Serviço</h5>
            </div>
            <div class="chart-body">
                <canvas id="chamadosPorTipoServicoChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Chamados por Dia da Semana</h5>
            </div>
            <div class="chart-body">
                <canvas id="chamadosPorDiaSemanaChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chamados Recentes -->
    <div class="recent-tickets-card">
        <div class="recent-tickets-header">
            <h5 class="recent-tickets-title">Chamados Recentes</h5>
            <a href="<?= base_url('chamados/listar') ?>" class="view-all-link">
                <i class="fas fa-external-link-alt"></i> Ver Todos
            </a>
        </div>
        <div class="recent-tickets-body">
            <div class="table-responsive">
                <table class="recent-tickets-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Solicitante</th>
                            <th>Descrição</th>
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
                                    <td class="ticket-id"><?= $chamado['id'] ?></td>
                                    <td class="ticket-user"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                    <td>
                                        <div class="ticket-desc" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                            <?= htmlspecialchars(substr($chamado['descricao'], 0, 30)) . (strlen($chamado['descricao']) > 30 ? '...' : '') ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($chamado['setor']) ?></td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?= $chamado['status_cor'] ?? '#4361ee' ?>">
                                            <?= htmlspecialchars($chamado['status']) ?>
                                        </span>
                                    </td>
                                    <td class="ticket-date"><?= date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])) ?></td>
                                    <td>
                                        <div class="ticket-actions">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="action-btn view" data-bs-toggle="tooltip" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-tickets">
                                    <div class="no-data-message">
                                        <i class="fas fa-ticket-alt"></i>
                                        <p>Nenhum chamado encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CSS para o Dashboard -->
<style>
    /* Estilos gerais do dashboard */
    .dashboard-container {
        padding: 1rem 0;
    }

    /* Cabeçalho */
    .user-dashboard-header {
        margin-bottom: 1.5rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .title-section {
        flex: 1;
    }

    .page-title {
        display: flex;
        align-items: center;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #4361ee;
    }

    .title-icon {
        color: #4361ee;
        font-size: 1.8rem;
        margin-right: 0.75rem;
    }

    .subtitle {
        color: #6c757d;
        font-size: 0.95rem;
        margin: 0;
    }

    .btn-new-ticket {
        display: flex;
        align-items: center;
        padding: 0.5rem 1.25rem;
        background-color: #2ecc71;
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-new-ticket:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
    }

    .btn-new-ticket i {
        margin-right: 0.5rem;
    }

    /* Cards de estatísticas - MODIFICADO */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        /* Força exatamente 6 colunas */
        gap: 0.75rem;
        /* Reduz o espaçamento entre os cards */
        margin-bottom: 1.5rem;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        /* Reduz o padding para caber melhor */
        border-radius: 10px;
        background-color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .stat-card.animate-in {
        opacity: 1;
        transform: translateY(0);
    }

    .stat-icon {
        width: 50px;
        /* Reduz o tamanho do ícone */
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        /* Reduz a margem */
        font-size: 1.25rem;
        /* Reduz o tamanho da fonte do ícone */
    }

    .total-card .stat-icon {
        background-color: #eef2ff;
        color: #4361ee;
    }

    .warning-card .stat-icon {
        background-color: #fff8e6;
        color: #f39c12;
    }

    .info-card .stat-icon {
        background-color: #e6f7ff;
        color: #3498db;
    }

    .success-card .stat-icon {
        background-color: #e8f8f0;
        color: #2ecc71;
    }

    .success-light-card .stat-icon {
        background-color: #f0fff4;
        color: #27ae60;
    }

    .primary-light-card .stat-icon {
        background-color: #f0f4ff;
        color: #3a56d4;
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 1.5rem;
        /* Reduz o tamanho da fonte do valor */
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #2c3e50;
    }

    .stat-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #2c3e50;
    }

    .stat-description {
        font-size: 0.75rem;
        /* Reduz o tamanho da fonte da descrição */
        color: #6c757d;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Gráficos */
    .charts-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f1f1;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }

    .chart-body {
        padding: 1.5rem;
        height: 300px;
    }

    /* Tabela de chamados recentes */
    .recent-tickets-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .recent-tickets-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .recent-tickets-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f1f1;
    }

    .recent-tickets-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }

    .view-all-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #4361ee;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .view-all-link:hover {
        color: #3a56d4;
        transform: translateX(3px);
    }

    .recent-tickets-body {
        overflow: hidden;
    }

    .recent-tickets-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-tickets-table th {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #f1f1f1;
        white-space: nowrap;
    }

    .recent-tickets-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }

    .recent-tickets-table tr:last-child td {
        border-bottom: none;
    }

    .recent-tickets-table tr:hover {
        background-color: #f8f9fa;
    }

    .ticket-id {
        font-weight: 600;
        color: #4361ee;
    }

    .ticket-user {
        font-weight: 500;
    }

    .ticket-desc {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }

    .ticket-date {
        color: #6c757d;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .ticket-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .action-btn.view {
        background-color: #4361ee;
        color: white;
    }

    .action-btn.view:hover {
        background-color: #3a56d4;
        transform: translateY(-2px);
    }

    .no-tickets {
        padding: 3rem 1.5rem;
    }

    .no-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #6c757d;
    }

    .no-data-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .no-data-message p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Responsividade */
    @media (max-width: 1400px) {
        .stats-cards {
            grid-template-columns: repeat(3, 1fr);
            /* 3 cards por linha em telas médias */
        }
    }

    @media (max-width: 1199.98px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            justify-content: space-between;
        }

        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
            /* 2 cards por linha em telas pequenas */
        }

        .recent-tickets-table th:nth-child(3),
        .recent-tickets-table td:nth-child(3) {
            display: none;
        }
    }

    @media (max-width: 767.98px) {
        .btn-new-ticket {
            width: 100%;
            justify-content: center;
        }

        .recent-tickets-table th:nth-child(4),
        .recent-tickets-table td:nth-child(4) {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .stats-cards {
            grid-template-columns: 1fr;
            /* 1 card por linha em telas muito pequenas */
        }

        .recent-tickets-table th:nth-child(6),
        .recent-tickets-table td:nth-child(6) {
            display: none;
        }

        .recent-tickets-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>

<!-- Script para inicializar e atualizar os gráficos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Referências aos gráficos
        let charts = {};

        // Inicializa o gráfico de chamados por status
        const statusCtx = document.getElementById('chamadosPorStatusChart').getContext('2d');
        charts.chamadosPorStatusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chamadosPorStatus['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Chamados por Status',
                    data: <?= json_encode($chamadosPorStatus['data'] ?? []) ?>,
                    backgroundColor: <?= json_encode($chamadosPorStatus['backgroundColor'] ?? []) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Inicializa o gráfico de chamados por setor
        const setorCtx = document.getElementById('chamadosPorSetorChart').getContext('2d');
        charts.chamadosPorSetorChart = new Chart(setorCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chamadosPorSetor['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Chamados por Setor',
                    data: <?= json_encode($chamadosPorSetor['data'] ?? []) ?>,
                    backgroundColor: <?= json_encode($chamadosPorSetor['backgroundColor'] ?? []) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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

        // Inicializa o gráfico de chamados por mês
        const mesCtx = document.getElementById('chamadosPorMesChart').getContext('2d');
        charts.chamadosPorMesChart = new Chart(mesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chamadosPorMes['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Chamados por Mês',
                    data: <?= json_encode($chamadosPorMes['data'] ?? []) ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderColor: '#4361ee',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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

        // Inicializa o gráfico de tempo médio por setor
        const tempoCtx = document.getElementById('tempoMedioPorSetorChart').getContext('2d');
        charts.tempoMedioPorSetorChart = new Chart(tempoCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($tempoMedioPorSetor['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Tempo Médio (horas)',
                    data: <?= json_encode($tempoMedioPorSetor['data'] ?? []) ?>,
                    backgroundColor: <?= json_encode($tempoMedioPorSetor['backgroundColor'] ?? []) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Inicializa o gráfico de chamados por tipo de serviço
        const tipoServicoCtx = document.getElementById('chamadosPorTipoServicoChart').getContext('2d');
        charts.chamadosPorTipoServicoChart = new Chart(tipoServicoCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($chamadosPorTipoServico['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Chamados por Tipo de Serviço',
                    data: <?= json_encode($chamadosPorTipoServico['data'] ?? []) ?>,
                    backgroundColor: <?= json_encode($chamadosPorTipoServico['backgroundColor'] ?? []) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Inicializa o gráfico de chamados por dia da semana
        const diaSemanaCtx = document.getElementById('chamadosPorDiaSemanaChart').getContext('2d');
        charts.chamadosPorDiaSemanaChart = new Chart(diaSemanaCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chamadosPorDiaSemana['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Chamados por Dia da Semana',
                    data: <?= json_encode($chamadosPorDiaSemana['data'] ?? []) ?>,
                    backgroundColor: <?= json_encode($chamadosPorDiaSemana['backgroundColor'] ?? []) ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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

        // Função para atualizar os dados dos gráficos via AJAX
        function atualizarGraficos() {
            fetch('<?= base_url('dashboard/getChartData') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dados atualizados:', data);

                    // Atualiza os contadores
                    document.getElementById('total-chamados').textContent = data.estatisticas.total;
                    document.getElementById('chamados-abertos').textContent = data.estatisticas.abertos;
                    document.getElementById('chamados-andamento').textContent = data.estatisticas.em_andamento;
                    document.getElementById('chamados-concluidos').textContent = data.estatisticas.concluidos;
                    document.getElementById('concluidos-hoje').textContent = data.estatisticas.concluidos_hoje;
                    document.getElementById('tempo-medio').textContent = data.estatisticas.tempo_medio_atendimento + ' h';

                    // Atualiza o gráfico de chamados por status
                    if (data.chamadosPorStatus && data.chamadosPorStatus.labels && data.chamadosPorStatus.data) {
                        charts.chamadosPorStatusChart.data.labels = data.chamadosPorStatus.labels;
                        charts.chamadosPorStatusChart.data.datasets[0].data = data.chamadosPorStatus.data;
                        charts.chamadosPorStatusChart.data.datasets[0].backgroundColor = data.chamadosPorStatus.backgroundColor;
                        charts.chamadosPorStatusChart.update();
                    }

                    // Atualiza o gráfico de chamados por setor
                    if (data.chamadosPorSetor && data.chamadosPorSetor.labels && data.chamadosPorSetor.data) {
                        charts.chamadosPorSetorChart.data.labels = data.chamadosPorSetor.labels;
                        charts.chamadosPorSetorChart.data.datasets[0].data = data.chamadosPorSetor.data;
                        charts.chamadosPorSetorChart.data.datasets[0].backgroundColor = data.chamadosPorSetor.backgroundColor;
                        charts.chamadosPorSetorChart.update();
                    }

                    // Atualiza o gráfico de chamados por mês
                    if (data.chamadosPorMes && data.chamadosPorMes.labels && data.chamadosPorMes.data) {
                        charts.chamadosPorMesChart.data.labels = data.chamadosPorMes.labels;
                        charts.chamadosPorMesChart.data.datasets[0].data = data.chamadosPorMes.data;
                        charts.chamadosPorMesChart.update();
                    }

                    // Atualiza o gráfico de tempo médio por setor
                    if (data.tempoMedioPorSetor && data.tempoMedioPorSetor.labels && data.tempoMedioPorSetor.data) {
                        charts.tempoMedioPorSetorChart.data.labels = data.tempoMedioPorSetor.labels;
                        charts.tempoMedioPorSetorChart.data.datasets[0].data = data.tempoMedioPorSetor.data;
                        charts.tempoMedioPorSetorChart.data.datasets[0].backgroundColor = data.tempoMedioPorSetor.backgroundColor;
                        charts.tempoMedioPorSetorChart.update();
                    }

                    // Atualiza o gráfico de chamados por tipo de serviço
                    if (data.chamadosPorTipoServico && data.chamadosPorTipoServico.labels && data.chamadosPorTipoServico.data) {
                        charts.chamadosPorTipoServicoChart.data.labels = data.chamadosPorTipoServico.labels;
                        charts.chamadosPorTipoServicoChart.data.datasets[0].data = data.chamadosPorTipoServico.data;
                        charts.chamadosPorTipoServicoChart.data.datasets[0].backgroundColor = data.chamadosPorTipoServico.backgroundColor;
                        charts.chamadosPorTipoServicoChart.update();
                    }

                    // Atualiza o gráfico de chamados por dia da semana
                    if (data.chamadosPorDiaSemana && data.chamadosPorDiaSemana.labels && data.chamadosPorDiaSemana.data) {
                        charts.chamadosPorDiaSemanaChart.data.labels = data.chamadosPorDiaSemana.labels;
                        charts.chamadosPorDiaSemanaChart.data.datasets[0].data = data.chamadosPorDiaSemana.data;
                        charts.chamadosPorDiaSemanaChart.data.datasets[0].backgroundColor = data.chamadosPorDiaSemana.backgroundColor;
                        charts.chamadosPorDiaSemanaChart.update();
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar gráficos:', error);
                });
        }

        // Atualiza os gráficos a cada 5 minutos
        setInterval(atualizarGraficos, 300000);

        // Animação para os cards de estatísticas
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(function(card, index) {
            setTimeout(function() {
                card.classList.add('animate-in');
            }, index * 100);
        });
    });
</script>