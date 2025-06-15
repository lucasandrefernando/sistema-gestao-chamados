<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        Setor: <?= htmlspecialchars($setor['nome']) ?>
        <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?> ms-2">
            <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
        </span>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('setores/visualizacao') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row mb-4">
    <!-- Informações do Setor -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Setor</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($setor['descricao'])): ?>
                    <p class="card-text"><?= nl2br(htmlspecialchars($setor['descricao'])) ?></p>
                <?php else: ?>
                    <p class="card-text text-muted"><em>Sem descrição</em></p>
                <?php endif; ?>

                <hr>

                <div class="mb-2">
                    <strong>Total de Chamados:</strong>
                    <span class="badge bg-primary"><?= $estatisticas['total_chamados'] ?></span>
                </div>

                <?php if (!empty($estatisticas['tempo_medio_atendimento'])): ?>
                    <div class="mb-2">
                        <strong>Tempo Médio de Atendimento:</strong>
                        <span class="badge bg-info"><?= formatarTempo($estatisticas['tempo_medio_atendimento']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Status -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Status</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($estatisticas['chamados_por_status'])): ?>
                    <div style="height: 250px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Prioridade -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Tipo de Serviço</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($estatisticas['chamados_por_prioridade'])): ?>
                    <div style="height: 250px; position: relative;">
                        <canvas id="priorityChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Gráfico de Chamados por Mês -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Mês</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($estatisticas['chamados_por_mes'])): ?>
                    <div style="height: 300px; position: relative;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Usuários Mais Ativos -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Solicitantes Mais Ativos</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($estatisticas['usuarios_mais_ativos'])): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($estatisticas['usuarios_mais_ativos'] as $usuario): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($usuario['nome']) ?>
                                <span class="badge bg-primary rounded-pill"><?= $usuario['total'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center text-muted mt-5">Sem dados para exibir</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Chamados -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Chamados do Setor</h5>
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
                            <th>Status</th>
                            <th>Data de Solicitação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chamados as $chamado): ?>
                            <tr>
                                <td><?= isset($chamado['id']) ? $chamado['id'] : 'N/A' ?></td>
                                <td><?= isset($chamado['titulo']) ? htmlspecialchars(substr($chamado['titulo'], 0, 50)) . (strlen($chamado['titulo']) > 50 ? '...' : '') : 'N/A' ?></td>
                                <td><?= isset($chamado['solicitante_nome']) ? htmlspecialchars($chamado['solicitante_nome']) : 'N/A' ?></td>
                                <td>
                                    <?php if (isset($chamado['status'])): ?>
                                        <span class="badge bg-<?= getStatusColor($chamado['status']) ?>">
                                            <?= formatarStatus($chamado['status']) ?>
                                        </span>
                                    <?php elseif (isset($chamado['status_nome'])): ?>
                                        <span class="badge bg-<?= getStatusColor(strtolower(str_replace(' ', '_', $chamado['status_nome']))) ?>">
                                            <?= $chamado['status_nome'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Desconhecido</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= isset($chamado['data_abertura']) ? formatarData($chamado['data_abertura']) : 'N/A' ?></td>
                                <td>
                                    <?php if (isset($chamado['id'])): ?>
                                        <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Nenhum chamado encontrado para este setor.
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de Status
        <?php if (!empty($estatisticas['chamados_por_status'])): ?>
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php foreach ($estatisticas['chamados_por_status'] as $status): ?> '<?= isset($status['nome']) ? $status['nome'] : formatarStatus($status['status']) ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($estatisticas['chamados_por_status'] as $status): ?>
                                <?= $status['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            <?php foreach ($estatisticas['chamados_por_status'] as $status): ?>
                                getStatusColorRGBA('<?= isset($status['status']) ? $status['status'] : strtolower(str_replace(' ', '_', $status['nome'])) ?>'),
                            <?php endforeach; ?>
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

        // Gráfico de Prioridade (Tipo de Serviço)
        <?php if (!empty($estatisticas['chamados_por_prioridade'])): ?>
            const priorityCtx = document.getElementById('priorityChart').getContext('2d');
            const priorityChart = new Chart(priorityCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php foreach ($estatisticas['chamados_por_prioridade'] as $prioridade): ?> '<?= isset($prioridade['nome']) ? $prioridade['nome'] : formatarPrioridade($prioridade['prioridade']) ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($estatisticas['chamados_por_prioridade'] as $prioridade): ?>
                                <?= $prioridade['total'] ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(108, 17, 25, 0.7)',
                            'rgba(0, 123, 255, 0.7)',
                            'rgba(111, 66, 193, 0.7)'
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

        // Gráfico de Chamados por Mês
        <?php if (!empty($estatisticas['chamados_por_mes'])): ?>
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($estatisticas['chamados_por_mes'] as $mes): ?> '<?= $mes['mes_ano'] ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Chamados',
                        data: [
                            <?php foreach ($estatisticas['chamados_por_mes'] as $mes): ?>
                                <?= $mes['total'] ?>,
                            <?php endforeach; ?>
                        ],
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
        <?php endif; ?>
    });

    // Função auxiliar para obter a cor do status
    function getStatusColor(status) {
        const colors = {
            'aberto': 'danger',
            'em_andamento': 'warning',
            'pausado': 'info',
            'concluido': 'success',
            'cancelado': 'secondary'
        };

        return colors[status] || 'primary';
    }

    // Função auxiliar para obter a cor RGBA do status
    function getStatusColorRGBA(status) {
        const colors = {
            'aberto': 'rgba(220, 53, 69, 0.7)',
            'em_andamento': 'rgba(255, 193, 7, 0.7)',
            'pausado': 'rgba(23, 162, 184, 0.7)',
            'concluido': 'rgba(40, 167, 69, 0.7)',
            'cancelado': 'rgba(108, 117, 125, 0.7)'
        };

        return colors[status] || 'rgba(0, 123, 255, 0.7)';
    }

    // Função auxiliar para formatar status
    function formatarStatus(status) {
        const formatado = {
            'aberto': 'Aberto',
            'em_andamento': 'Em Andamento',
            'pausado': 'Pausado',
            'concluido': 'Concluído',
            'cancelado': 'Cancelado'
        };

        return formatado[status] || status;
    }

    // Função auxiliar para formatar prioridade
    function formatarPrioridade(prioridade) {
        return prioridade.charAt(0).toUpperCase() + prioridade.slice(1).replace(/_/g, ' ');
    }
</script>