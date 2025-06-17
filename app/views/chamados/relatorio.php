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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Filtros</h5>
        <!-- Botão para mostrar/ocultar filtros com contador de filtros ativos -->
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="true" aria-controls="collapseFilters">
            <i class="fas fa-filter me-1"></i> Filtros
            <?php
            // Conta quantos filtros estão ativos
            $filtrosAtivosCount = 0;
            if (!empty($filtros['ano']) && $filtros['ano'] != date('Y')) $filtrosAtivosCount++;
            if (!empty($filtros['mes'])) $filtrosAtivosCount++;
            if (!empty($filtros['setor'])) $filtrosAtivosCount++;
            if (!empty($filtros['status'])) $filtrosAtivosCount++;
            if (!empty($filtros['tipo_servico'])) $filtrosAtivosCount++;
            if (!empty($filtros['solicitante'])) $filtrosAtivosCount++;
            if (!empty($filtros['data_inicio'])) $filtrosAtivosCount++;
            if (!empty($filtros['data_fim'])) $filtrosAtivosCount++;

            // Exibe o contador se houver filtros ativos
            if ($filtrosAtivosCount > 0) {
                echo '<span class="badge bg-primary ms-1">' . $filtrosAtivosCount . '</span>';
            }
            ?>
        </button>
    </div>
    <div class="collapse show" id="collapseFilters">
        <div class="card-body">
            <form action="<?= base_url('chamados/relatorio') ?>" method="get" class="row g-3">
                <!-- Período Predefinido (Novo) -->
                <div class="col-md-3">
                    <label for="periodo" class="form-label">Período Predefinido</label>
                    <select class="form-select" id="periodo" name="periodo" onchange="aplicarPeriodo()">
                        <option value="">Selecione um período...</option>
                        <option value="hoje">Hoje</option>
                        <option value="ontem">Ontem</option>
                        <option value="7dias">Últimos 7 dias</option>
                        <option value="30dias">Últimos 30 dias</option>
                        <option value="este_mes">Este mês</option>
                        <option value="mes_anterior">Mês anterior</option>
                        <option value="este_ano">Este ano</option>
                    </select>
                </div>

                <!-- Primeira linha de filtros -->
                <div class="col-md-3">
                    <label for="ano" class="form-label">Ano</label>
                    <select class="form-select <?= (!empty($filtros['ano']) && $filtros['ano'] != date('Y')) ? 'filtro-ativo' : '' ?>" id="ano" name="ano">
                        <?php foreach ($anosDisponiveis as $ano): ?>
                            <option value="<?= $ano ?>" <?= $filtros['ano'] == $ano ? 'selected' : '' ?>>
                                <?= $ano ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="mes" class="form-label">Mês</label>
                    <select class="form-select <?= !empty($filtros['mes']) ? 'filtro-ativo' : '' ?>" id="mes" name="mes">
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
                <div class="col-md-3">
                    <label for="setor" class="form-label">Setor</label>
                    <select class="form-select <?= !empty($filtros['setor']) ? 'filtro-ativo' : '' ?>" id="setor" name="setor">
                        <option value="">Todos os setores</option>
                        <?php foreach ($setores as $setor): ?>
                            <option value="<?= $setor['id'] ?>" <?= $filtros['setor'] == $setor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($setor['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Segunda linha de filtros -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select <?= !empty($filtros['status']) ? 'filtro-ativo' : '' ?>" id="status" name="status">
                        <option value="">Todos os status</option>
                        <?php foreach ($statusList as $status): ?>
                            <option value="<?= $status['id'] ?>" <?= isset($filtros['status']) && $filtros['status'] == $status['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                    <select class="form-select <?= !empty($filtros['tipo_servico']) ? 'filtro-ativo' : '' ?>" id="tipo_servico" name="tipo_servico">
                        <option value="">Todos os tipos</option>
                        <?php if (isset($tiposServico) && is_array($tiposServico)): ?>
                            <?php foreach ($tiposServico as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>" <?= isset($filtros['tipo_servico']) && $filtros['tipo_servico'] == $tipo ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="solicitante" class="form-label">Solicitante</label>
                    <select class="form-select <?= !empty($filtros['solicitante']) ? 'filtro-ativo' : '' ?>" id="solicitante" name="solicitante">
                        <option value="">Todos os solicitantes</option>
                        <?php if (isset($solicitantes) && is_array($solicitantes)): ?>
                            <?php foreach ($solicitantes as $solicitante): ?>
                                <option value="<?= htmlspecialchars($solicitante) ?>" <?= isset($filtros['solicitante']) && $filtros['solicitante'] == $solicitante ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($solicitante) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control <?= !empty($filtros['data_inicio']) ? 'filtro-ativo' : '' ?>" id="data_inicio" name="data_inicio"
                        value="<?= isset($filtros['data_inicio']) ? $filtros['data_inicio'] : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data Final</label>
                    <input type="date" class="form-control <?= !empty($filtros['data_fim']) ? 'filtro-ativo' : '' ?>" id="data_fim" name="data_fim"
                        value="<?= isset($filtros['data_fim']) ? $filtros['data_fim'] : '' ?>">
                </div>

                <!-- Botões -->
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                    <a href="<?= base_url('chamados/relatorio') ?>" class="btn btn-secondary">
                        <i class="fas fa-eraser me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filtros Ativos -->
<?php
$filtrosAtivos = [];

// Verifica quais filtros estão ativos
if (!empty($filtros['ano']) && $filtros['ano'] != date('Y')) {
    $filtrosAtivos[] = ['label' => 'Ano', 'value' => $filtros['ano'], 'param' => 'ano'];
}

if (!empty($filtros['mes'])) {
    $meses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];
    $filtrosAtivos[] = ['label' => 'Mês', 'value' => $meses[$filtros['mes']], 'param' => 'mes'];
}

if (!empty($filtros['setor'])) {
    $setorNome = '';
    foreach ($setores as $setor) {
        if ($setor['id'] == $filtros['setor']) {
            $setorNome = $setor['nome'];
            break;
        }
    }
    $filtrosAtivos[] = ['label' => 'Setor', 'value' => $setorNome, 'param' => 'setor'];
}

if (!empty($filtros['status'])) {
    $statusNome = '';
    foreach ($statusList as $status) {
        if ($status['id'] == $filtros['status']) {
            $statusNome = $status['nome'];
            break;
        }
    }
    $filtrosAtivos[] = ['label' => 'Status', 'value' => $statusNome, 'param' => 'status'];
}

if (!empty($filtros['tipo_servico'])) {
    $filtrosAtivos[] = ['label' => 'Tipo de Serviço', 'value' => $filtros['tipo_servico'], 'param' => 'tipo_servico'];
}

if (!empty($filtros['solicitante'])) {
    $filtrosAtivos[] = ['label' => 'Solicitante', 'value' => $filtros['solicitante'], 'param' => 'solicitante'];
}

if (!empty($filtros['data_inicio'])) {
    $filtrosAtivos[] = ['label' => 'Data Inicial', 'value' => date('d/m/Y', strtotime($filtros['data_inicio'])), 'param' => 'data_inicio'];
}

if (!empty($filtros['data_fim'])) {
    $filtrosAtivos[] = ['label' => 'Data Final', 'value' => date('d/m/Y', strtotime($filtros['data_fim'])), 'param' => 'data_fim'];
}

// Exibe os filtros ativos
if (!empty($filtrosAtivos)):
?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-filter me-2"></i>
            <strong>Filtros aplicados:</strong>
            <a href="<?= base_url('chamados/relatorio') ?>" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fas fa-times me-1"></i> Limpar Todos
            </a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($filtrosAtivos as $filtro): ?>
                <div class="filtro-badge">
                    <span class="badge-label"><?= htmlspecialchars($filtro['label']) ?>:</span>
                    <span class="badge-value"><?= htmlspecialchars($filtro['value']) ?></span>

                    <?php
                    // Cria uma cópia dos filtros atuais
                    $filtrosSemEste = $filtros;
                    // Remove o filtro atual
                    unset($filtrosSemEste[$filtro['param']]);
                    // Gera a URL sem este filtro
                    $urlSemFiltro = base_url('chamados/relatorio?' . http_build_query($filtrosSemEste));
                    ?>

                    <a href="<?= $urlSemFiltro ?>" class="badge-remove" title="Remover filtro">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

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
                    <canvas id="mensalChart"></canvas>
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

        // Inicializa o collapse do Bootstrap
        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            const collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
            collapseElementList.map(function(collapseEl) {
                return new bootstrap.Collapse(collapseEl, {
                    toggle: false
                });
            });
        }

        // Inicializa tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Destaca visualmente os filtros ativos
        destacarFiltrosAtivos();

        // Submete o formulário quando certos filtros mudarem
        const filtrosAutoSubmit = document.querySelectorAll('#ano, #mes, #setor');
        filtrosAutoSubmit.forEach(function(filtro) {
            filtro.addEventListener('change', function() {
                // Desativa o período predefinido ao mudar manualmente
                const periodoSelect = document.getElementById('periodo');
                if (periodoSelect) {
                    periodoSelect.value = '';
                }

                // Submete o formulário
                this.closest('form').submit();
            });
        });

        // Sincroniza datas
        const dataInicio = document.getElementById('data_inicio');
        const dataFim = document.getElementById('data_fim');

        if (dataInicio && dataFim) {
            dataInicio.addEventListener('change', function() {
                if (dataFim.value && this.value > dataFim.value) {
                    dataFim.value = this.value;
                }
            });

            dataFim.addEventListener('change', function() {
                if (dataInicio.value && this.value < dataInicio.value) {
                    dataInicio.value = this.value;
                }
            });
        }
    });

    /**
     * Destaca visualmente os filtros ativos
     */
    function destacarFiltrosAtivos() {
        const formControls = document.querySelectorAll('.form-select, .form-control');
        formControls.forEach(function(control) {
            if (control.value && control.value !== '' && control.id !== 'ano') {
                control.classList.add('filtro-ativo');

                // Adiciona um ícone de filtro ativo
                const formGroup = control.closest('.col-md-3, .col-md-4');
                if (formGroup) {
                    const label = formGroup.querySelector('.form-label');
                    if (label && !label.querySelector('.filtro-ativo-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-filter ms-1 text-primary filtro-ativo-icon';
                        icon.style.fontSize = '0.75rem';
                        label.appendChild(icon);
                    }
                }
            } else if (control.id === 'ano' && control.value !== '' && control.value != new Date().getFullYear()) {
                // Destaca o ano apenas se for diferente do ano atual
                control.classList.add('filtro-ativo');

                const formGroup = control.closest('.col-md-3, .col-md-4');
                if (formGroup) {
                    const label = formGroup.querySelector('.form-label');
                    if (label && !label.querySelector('.filtro-ativo-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-filter ms-1 text-primary filtro-ativo-icon';
                        icon.style.fontSize = '0.75rem';
                        label.appendChild(icon);
                    }
                }
            }
        });
    }

    /**
     * Aplica um período predefinido aos campos de data
     */
    function aplicarPeriodo() {
        const periodo = document.getElementById('periodo').value;
        const dataInicio = document.getElementById('data_inicio');
        const dataFim = document.getElementById('data_fim');
        const ano = document.getElementById('ano');
        const mes = document.getElementById('mes');

        // Limpa as datas
        if (periodo === '') {
            dataInicio.value = '';
            dataFim.value = '';
            return;
        }

        // Data atual
        const hoje = new Date();
        const formatoData = (data) => {
            const ano = data.getFullYear();
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const dia = String(data.getDate()).padStart(2, '0');
            return `${ano}-${mes}-${dia}`;
        };

        // Aplica o período selecionado
        switch (periodo) {
            case 'hoje':
                dataInicio.value = formatoData(hoje);
                dataFim.value = formatoData(hoje);
                break;

            case 'ontem':
                const ontem = new Date(hoje);
                ontem.setDate(hoje.getDate() - 1);
                dataInicio.value = formatoData(ontem);
                dataFim.value = formatoData(ontem);
                break;

            case '7dias':
                const seteDiasAtras = new Date(hoje);
                seteDiasAtras.setDate(hoje.getDate() - 7);
                dataInicio.value = formatoData(seteDiasAtras);
                dataFim.value = formatoData(hoje);
                break;

            case '30dias':
                const trintaDiasAtras = new Date(hoje);
                trintaDiasAtras.setDate(hoje.getDate() - 30);
                dataInicio.value = formatoData(trintaDiasAtras);
                dataFim.value = formatoData(hoje);
                break;

            case 'este_mes':
                const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                dataInicio.value = formatoData(primeiroDiaMes);
                dataFim.value = formatoData(hoje);

                // Seleciona o mês atual no dropdown
                if (mes) mes.value = hoje.getMonth() + 1;
                if (ano) ano.value = hoje.getFullYear();
                break;

            case 'mes_anterior':
                const primeiroDiaMesAnterior = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
                const ultimoDiaMesAnterior = new Date(hoje.getFullYear(), hoje.getMonth(), 0);
                dataInicio.value = formatoData(primeiroDiaMesAnterior);
                dataFim.value = formatoData(ultimoDiaMesAnterior);

                // Seleciona o mês anterior no dropdown
                if (mes) mes.value = hoje.getMonth() === 0 ? 12 : hoje.getMonth();
                if (ano) ano.value = hoje.getMonth() === 0 ? hoje.getFullYear() - 1 : hoje.getFullYear();
                break;

            case 'este_ano':
                const primeiroDiaAno = new Date(hoje.getFullYear(), 0, 1);
                dataInicio.value = formatoData(primeiroDiaAno);
                dataFim.value = formatoData(hoje);

                // Seleciona o ano atual no dropdown e limpa o mês
                if (ano) ano.value = hoje.getFullYear();
                if (mes) mes.value = '';
                break;
        }

        // Destaca os campos preenchidos
        if (dataInicio.value) dataInicio.classList.add('filtro-ativo');
        if (dataFim.value) dataFim.classList.add('filtro-ativo');

        // Adiciona ícones aos labels
        adicionarIconesFiltroAtivo();
    }

    /**
     * Adiciona ícones aos labels dos filtros ativos
     */
    function adicionarIconesFiltroAtivo() {
        const formControls = document.querySelectorAll('.filtro-ativo');
        formControls.forEach(function(control) {
            const formGroup = control.closest('.col-md-3, .col-md-4');
            if (formGroup) {
                const label = formGroup.querySelector('.form-label');
                if (label && !label.querySelector('.filtro-ativo-icon')) {
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-filter ms-1 text-primary filtro-ativo-icon';
                    icon.style.fontSize = '0.75rem';
                    label.appendChild(icon);
                }
            }
        });
    }
</script>