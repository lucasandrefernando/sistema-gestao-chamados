<div class="page-header">
    <h1 class="page-title">Relatório de Chamados</h1>
    <div class="btn-toolbar">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados/listar') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Listar Chamados
            </a>
            <a href="<?= base_url('chamados') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
        </div>
        <div class="btn-group">
            <a href="<?= base_url('chamados/exportar-relatorio?' . http_build_query($filtros)) ?>" class="btn btn-success">
                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
            </a>
            <a href="<?= base_url('chamados/criar') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Novo Chamado
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('chamados/relatorio') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="ano" class="form-label">Ano</label>
                <select class="form-select" id="ano" name="ano">
                    <?php foreach ($anosDisponiveis as $ano): ?>
                        <option value="<?= $ano ?>" <?= $filtros['ano'] == $ano ? 'selected' : '' ?>>
                            <?= $ano ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="mes" class="form-label">Mês</label>
                <select class="form-select" id="mes" name="mes">
                    <option value="">Todos os meses</option>
                    <option value="1" <?= $filtros['mes'] == 1 ? 'selected' : '' ?>>Janeiro</option>
                    <option value="2" <?= $filtros['mes'] == 2 ? 'selected' : '' ?>>Fevereiro</option>
                    <option value="3" <?= $filtros['mes'] == 3 ? 'selected' : '' ?>>Março</option>
                    <option value="4" <?= $filtros['mes'] == 4 ? 'selected' : '' ?>>Abril</option>
                    <option value="5" <?= $filtros['mes'] == 5 ? 'selected' : '' ?>>Maio</option>
                    <option value="6" <?= $filtros['mes'] == 6 ? 'selected' : '' ?>>Junho</option>
                    <option value="7" <?= $filtros['mes'] == 7 ? 'selected' : '' ?>>Julho</option>
                    <option value="8" <?= $filtros['mes'] == 8 ? 'selected' : '' ?>>Agosto</option>
                    <option value="9" <?= $filtros['mes'] == 9 ? 'selected' : '' ?>>Setembro</option>
                    <option value="10" <?= $filtros['mes'] == 10 ? 'selected' : '' ?>>Outubro</option>
                    <option value="11" <?= $filtros['mes'] == 11 ? 'selected' : '' ?>>Novembro</option>
                    <option value="12" <?= $filtros['mes'] == 12 ? 'selected' : '' ?>>Dezembro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="setor" class="form-label">Setor</label>
                <select class="form-select" id="setor" name="setor">
                    <option value="">Todos os setores</option>
                    <?php foreach ($setores as $setor): ?>
                        <option value="<?= $setor['id'] ?>" <?= $filtros['setor'] == $setor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($setor['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                <a href="<?= base_url('chamados/relatorio') ?>" class="btn btn-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<!-- Adicione isso à seção de depuração -->
<h6>Detalhes dos Chamados por Tipo de Serviço:</h6>
<ul>
    <?php foreach ($chamadosPorTipoServico['raw'] ?? [] as $tipo): ?>
        <li><?= htmlspecialchars($tipo['tipo_servico'] ?? 'N/A') ?>: <?= $tipo['total'] ?? 0 ?></li>
    <?php endforeach; ?>
</ul>


<!-- Estatísticas Gerais -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Estatísticas Gerais</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center mb-3">
                            <h6 class="text-muted mb-1">Total de Chamados</h6>
                            <h2 class="mb-0"><?= $estatisticasGerais['total'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center mb-3">
                            <h6 class="text-muted mb-1">Concluídos</h6>
                            <h2 class="mb-0 text-success"><?= $estatisticasGerais['concluidos'] ?></h2>
                            <small class="text-muted"><?= $estatisticasGerais['taxa_conclusao'] ?>% do total</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center mb-3">
                            <h6 class="text-muted mb-1">Em Andamento</h6>
                            <h2 class="mb-0 text-warning"><?= $estatisticasGerais['em_andamento'] ?></h2>
                            <small class="text-muted"><?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['em_andamento'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>% do total</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center mb-3">
                            <h6 class="text-muted mb-1">Abertos</h6>
                            <h2 class="mb-0 text-danger"><?= $estatisticasGerais['abertos'] ?></h2>
                            <small class="text-muted"><?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['abertos'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>% do total</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center mb-3">
                            <h6 class="text-muted mb-1">Tempo Médio de Resolução</h6>
                            <h2 class="mb-0 text-primary">
                                <?php
                                $horas = $estatisticasGerais['tempo_medio'];
                                if ($horas < 24) {
                                    echo number_format($horas, 1) . ' h';
                                } else {
                                    $dias = floor($horas / 24);
                                    $horasRestantes = number_format(fmod($horas, 24), 1);
                                    echo $dias . 'd ' . $horasRestantes . 'h';
                                }
                                ?>
                            </h2>
                            <small class="text-muted">Da abertura até conclusão</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center mb-3">
                        <h6 class="text-muted mb-1">Chamados por Dia</h6>
                        <h2 class="mb-0 text-info">
                            <?php
                            // Calcula média diária de chamados no período
                            $diasPeriodo = $estatisticasGerais['dias_periodo'] ?? 30; // Padrão: 30 dias
                            echo round($estatisticasGerais['total'] / max(1, $diasPeriodo), 1);
                            ?>
                        </h2>
                        <small class="text-muted">Média no período</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Elemento oculto para armazenar dados dos gráficos -->
<div id="relatorio-data" style="display: none;"
    data-status='<?= json_encode($chamadosPorStatus) ?>'
    data-mensal='<?= json_encode($chamadosPorMes) ?>'
    data-tempo='<?= json_encode($tempoMedioAtendimento) ?>'
    data-setor='<?= json_encode($chamadosPorSetor) ?>'
    data-tipo='<?= json_encode($chamadosPorTipoServico) ?>'
    data-taxa-resolucao='<?= json_encode($taxaResolucao) ?>'
    data-dia-semana='<?= json_encode($chamadosPorDiaSemana) ?>'
    data-evolucao='<?= json_encode($evolucaoMensalPorStatus) ?>'>
</div>

<!-- Gráficos principais em duas colunas -->
<div class="row">
    <!-- Chamados por Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Status</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chamados por Mês -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Mês (<?= $filtros['ano'] ?>)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evolução Mensal por Status (linha completa) -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evolução Mensal por Status (<?= $filtros['ano'] ?>)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="evolucaoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Segunda linha de gráficos -->
<div class="row">
    <!-- Chamados por Dia da Semana -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Chamados por Dia da Semana</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="diaSemanaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Taxa de Resolução -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Taxa de Resolução</h5>
                <span class="badge bg-success"><?= $taxaResolucao['taxa_resolucao'] ?>% resolvidos</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="taxaResolucaoChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Total de chamados: <?= $taxaResolucao['total'] ?></small>
                    <small class="text-success">Concluídos: <?= $taxaResolucao['concluidos'] ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira linha de gráficos -->
    <div class="row">
        <!-- Tempo Médio de Atendimento -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tempo Médio de Atendimento</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="tempoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chamados por Setor -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chamados por Setor</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="setorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarta linha de gráficos -->
    <div class="row">
        <!-- Chamados por Tipo de Serviço -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Tipos de Serviço</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="tipoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Chamados por Status -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Dados: Chamados por Status</h5>
                    <span class="badge bg-primary"><?= $estatisticasGerais['total'] ?> chamados</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-center">Percentual</th>
                                    <th class="text-center">Tempo Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalChamados = array_sum($chamadosPorStatus['data'] ?? []);
                                foreach ($chamadosPorStatus['raw'] ?? [] as $index => $row):
                                    $percentual = $totalChamados > 0 ? ($row['total'] / $totalChamados) * 100 : 0;

                                    // Determina a cor do status
                                    $statusClass = 'secondary';
                                    $statusNome = strtolower($row['status_nome'] ?? '');
                                    if (strpos($statusNome, 'aberto') !== false) {
                                        $statusClass = 'danger';
                                    } elseif (strpos($statusNome, 'andamento') !== false || strpos($statusNome, 'atendimento') !== false) {
                                        $statusClass = 'warning';
                                    } elseif (strpos($statusNome, 'concluído') !== false || strpos($statusNome, 'resolvido') !== false) {
                                        $statusClass = 'success';
                                    } elseif (strpos($statusNome, 'cancelado') !== false) {
                                        $statusClass = 'dark';
                                    } elseif (strpos($statusNome, 'pendente') !== false || strpos($statusNome, 'pausado') !== false) {
                                        $statusClass = 'info';
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars($row['status_nome'] ?? '') ?></span>
                                        </td>
                                        <td class="text-center"><?= $row['total'] ?? 0 ?></td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?= $statusClass ?>" role="progressbar"
                                                    style="width: <?= $percentual ?>%;"
                                                    aria-valuenow="<?= $percentual ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= number_format($percentual, 1) ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            // Busca o tempo médio para este status nos dados do gráfico de tempo
                                            $tempoMedio = null;
                                            foreach ($tempoMedioAtendimento['raw'] ?? [] as $tempoRow) {
                                                if (isset($tempoRow['status_id']) && isset($row['status_id']) && $tempoRow['status_id'] == $row['status_id']) {
                                                    $tempoMedio = (float)$tempoRow['tempo_medio'];
                                                    break;
                                                }
                                            }

                                            if ($tempoMedio !== null) {
                                                // Formata o tempo médio
                                                if ($tempoMedio < 24) {
                                                    echo number_format($tempoMedio, 1) . ' horas';
                                                } else {
                                                    $dias = floor($tempoMedio / 24);
                                                    $horasRestantes = number_format(fmod($tempoMedio, 24), 1);
                                                    echo $dias . 'd ' . $horasRestantes . 'h';
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($chamadosPorStatus['raw'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3">Nenhum dado encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script para inicializar os gráficos -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Verifica se o Chart.js está carregado
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js não está carregado. Verifique se a biblioteca está incluída.');
                    return;
                }

                // Inicializa os relatórios
                if (typeof initRelatorios === 'function') {
                    initRelatorios();
                } else {
                    console.error('A função initRelatorios não está definida. Verifique se o arquivo relatorios.js está incluído.');
                }
            });
        </script>