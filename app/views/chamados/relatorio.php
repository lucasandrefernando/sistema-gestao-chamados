<div class="chamados-relatorio">
    <!-- Cabeçalho da Página -->
    <div class="chamados-relatorio-header">
        <h1 class="chamados-relatorio-titulo">Relatório de Chamados</h1>
        <div class="chamados-relatorio-acoes">
            <div class="chamados-relatorio-acoes-grupo">
                <a href="<?= base_url('chamados/listar') ?>" class="chamados-relatorio-btn chamados-relatorio-btn-secundario">
                    <i class="fas fa-list"></i> Listar Chamados
                </a>
                <a href="<?= base_url('chamados') ?>" class="chamados-relatorio-btn chamados-relatorio-btn-secundario">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            <div class="chamados-relatorio-acoes-grupo">
                <a href="<?= base_url('chamados/exportar-relatorio?' . http_build_query($filtros)) ?>" class="chamados-relatorio-btn chamados-relatorio-btn-sucesso">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="<?= base_url('chamados/criar') ?>" class="chamados-relatorio-btn chamados-relatorio-btn-primario">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="chamados-relatorio-filtros-card">
        <div class="chamados-relatorio-filtros-header">
            <h2 class="chamados-relatorio-filtros-titulo">Filtros</h2>
            <!-- Botão para mostrar/ocultar filtros com contador de filtros ativos -->
            <button class="chamados-relatorio-btn chamados-relatorio-btn-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="true" aria-controls="collapseFilters">
                <i class="fas fa-filter"></i> Filtros
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
                    echo '<span class="chamados-relatorio-contador">' . $filtrosAtivosCount . '</span>';
                }
                ?>
            </button>
        </div>
        <div class="collapse show" id="collapseFilters">
            <div class="chamados-relatorio-filtros-body">
                <form action="<?= base_url('chamados/relatorio') ?>" method="get" class="chamados-relatorio-filtros-form">
                    <!-- Período Predefinido -->
                    <div class="chamados-relatorio-filtros-grupo">
                        <label for="periodo" class="chamados-relatorio-filtros-label">Período Predefinido</label>
                        <select class="chamados-relatorio-filtros-select" id="periodo" name="periodo" onchange="aplicarPeriodo()">
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
                    <div class="chamados-relatorio-filtros-grid">
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="ano" class="chamados-relatorio-filtros-label">
                                Ano
                                <?php if (!empty($filtros['ano']) && $filtros['ano'] != date('Y')): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= (!empty($filtros['ano']) && $filtros['ano'] != date('Y')) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="ano" name="ano">
                                <?php foreach ($anosDisponiveis as $ano): ?>
                                    <option value="<?= $ano ?>" <?= $filtros['ano'] == $ano ? 'selected' : '' ?>>
                                        <?= $ano ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="mes" class="chamados-relatorio-filtros-label">
                                Mês
                                <?php if (!empty($filtros['mes'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= !empty($filtros['mes']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="mes" name="mes">
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
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="setor" class="chamados-relatorio-filtros-label">
                                Setor
                                <?php if (!empty($filtros['setor'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= !empty($filtros['setor']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="setor" name="setor">
                                <option value="">Todos os setores</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id'] ?>" <?= $filtros['setor'] == $setor['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="status" class="chamados-relatorio-filtros-label">
                                Status
                                <?php if (!empty($filtros['status'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= !empty($filtros['status']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="status" name="status">
                                <option value="">Todos os status</option>
                                <?php foreach ($statusList as $status): ?>
                                    <option value="<?= $status['id'] ?>" <?= isset($filtros['status']) && $filtros['status'] == $status['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Segunda linha de filtros -->
                    <div class="chamados-relatorio-filtros-grid">
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="tipo_servico" class="chamados-relatorio-filtros-label">
                                Tipo de Serviço
                                <?php if (!empty($filtros['tipo_servico'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= !empty($filtros['tipo_servico']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="tipo_servico" name="tipo_servico">
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
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="solicitante" class="chamados-relatorio-filtros-label">
                                Solicitante
                                <?php if (!empty($filtros['solicitante'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <select class="chamados-relatorio-filtros-select <?= !empty($filtros['solicitante']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="solicitante" name="solicitante">
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
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="data_inicio" class="chamados-relatorio-filtros-label">
                                Data Inicial
                                <?php if (!empty($filtros['data_inicio'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <input type="date" class="chamados-relatorio-filtros-input <?= !empty($filtros['data_inicio']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="data_inicio" name="data_inicio"
                                value="<?= isset($filtros['data_inicio']) ? $filtros['data_inicio'] : '' ?>">
                        </div>
                        <div class="chamados-relatorio-filtros-grupo">
                            <label for="data_fim" class="chamados-relatorio-filtros-label">
                                Data Final
                                <?php if (!empty($filtros['data_fim'])): ?>
                                    <i class="fas fa-filter chamados-relatorio-filtro-ativo-icon"></i>
                                <?php endif; ?>
                            </label>
                            <input type="date" class="chamados-relatorio-filtros-input <?= !empty($filtros['data_fim']) ? 'chamados-relatorio-filtro-ativo' : '' ?>" id="data_fim" name="data_fim"
                                value="<?= isset($filtros['data_fim']) ? $filtros['data_fim'] : '' ?>">
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="chamados-relatorio-filtros-acoes">
                        <button type="submit" class="chamados-relatorio-btn chamados-relatorio-btn-primario">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                        <a href="<?= base_url('chamados/relatorio') ?>" class="chamados-relatorio-btn chamados-relatorio-btn-secundario">
                            <i class="fas fa-eraser"></i> Limpar
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
        <div class="chamados-relatorio-filtros-ativos">
            <div class="chamados-relatorio-filtros-ativos-header">
                <div class="chamados-relatorio-filtros-ativos-titulo">
                    <i class="fas fa-filter"></i>
                    <strong>Filtros aplicados:</strong>
                </div>
                <a href="<?= base_url('chamados/relatorio') ?>" class="chamados-relatorio-btn chamados-relatorio-btn-limpar">
                    <i class="fas fa-times"></i> Limpar Todos
                </a>
            </div>
            <div class="chamados-relatorio-filtros-ativos-lista">
                <?php foreach ($filtrosAtivos as $filtro): ?>
                    <div class="chamados-relatorio-filtro-badge">
                        <span class="chamados-relatorio-filtro-badge-label"><?= htmlspecialchars($filtro['label']) ?>:</span>
                        <span class="chamados-relatorio-filtro-badge-value"><?= htmlspecialchars($filtro['value']) ?></span>

                        <?php
                        // Cria uma cópia dos filtros atuais
                        $filtrosSemEste = $filtros;
                        // Remove o filtro atual
                        unset($filtrosSemEste[$filtro['param']]);
                        // Gera a URL sem este filtro
                        $urlSemFiltro = base_url('chamados/relatorio?' . http_build_query($filtrosSemEste));
                        ?>

                        <a href="<?= $urlSemFiltro ?>" class="chamados-relatorio-filtro-badge-remover" title="Remover filtro">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Estatísticas Gerais -->
    <div class="chamados-relatorio-estatisticas">
        <div class="chamados-relatorio-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Estatísticas Gerais</h2>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-estatisticas-grid">
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-total">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor"><?= $estatisticasGerais['total'] ?></h3>
                            <p class="chamados-relatorio-estatistica-label">Total de Chamados</p>
                        </div>
                    </div>
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-concluido">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor"><?= $estatisticasGerais['concluidos'] ?></h3>
                            <p class="chamados-relatorio-estatistica-label">Concluídos</p>
                            <div class="chamados-relatorio-estatistica-progresso">
                                <div class="chamados-relatorio-estatistica-barra" style="width: <?= $estatisticasGerais['taxa_conclusao'] ?>%"></div>
                                <span class="chamados-relatorio-estatistica-porcentagem"><?= $estatisticasGerais['taxa_conclusao'] ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-andamento">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor"><?= $estatisticasGerais['em_andamento'] ?></h3>
                            <p class="chamados-relatorio-estatistica-label">Em Andamento</p>
                            <div class="chamados-relatorio-estatistica-progresso">
                                <div class="chamados-relatorio-estatistica-barra chamados-relatorio-estatistica-barra-andamento"
                                    style="width: <?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['em_andamento'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>%"></div>
                                <span class="chamados-relatorio-estatistica-porcentagem">
                                    <?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['em_andamento'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-aberto">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor"><?= $estatisticasGerais['abertos'] ?></h3>
                            <p class="chamados-relatorio-estatistica-label">Abertos</p>
                            <div class="chamados-relatorio-estatistica-progresso">
                                <div class="chamados-relatorio-estatistica-barra chamados-relatorio-estatistica-barra-aberto"
                                    style="width: <?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['abertos'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>%"></div>
                                <span class="chamados-relatorio-estatistica-porcentagem">
                                    <?= $estatisticasGerais['total'] > 0 ? round(($estatisticasGerais['abertos'] / $estatisticasGerais['total']) * 100, 1) : 0 ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-tempo">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor">
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
                            </h3>
                            <p class="chamados-relatorio-estatistica-label">Tempo Médio de Resolução</p>
                            <small class="chamados-relatorio-estatistica-info">Da abertura até conclusão</small>
                        </div>
                    </div>
                    <div class="chamados-relatorio-estatistica">
                        <div class="chamados-relatorio-estatistica-icone chamados-relatorio-estatistica-icone-media">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="chamados-relatorio-estatistica-conteudo">
                            <h3 class="chamados-relatorio-estatistica-valor">
                                <?php
                                // Calcula média diária de chamados no período
                                $diasPeriodo = $estatisticasGerais['dias_periodo'] ?? 30; // Padrão: 30 dias
                                echo round($estatisticasGerais['total'] / max(1, $diasPeriodo), 1);
                                ?>
                            </h3>
                            <p class="chamados-relatorio-estatistica-label">Chamados por Dia</p>
                            <small class="chamados-relatorio-estatistica-info">Média no período</small>
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
    <div class="chamados-relatorio-graficos-grid">
        <!-- Chamados por Status -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Chamados por Status</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="statusChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="statusChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chamados por Mês -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Chamados por Mês (<?= $filtros['ano'] ?>)</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="mensalChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="mensalChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="mensalChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Evolução Mensal por Status (linha completa) -->
    <div class="chamados-relatorio-grafico-card chamados-relatorio-grafico-card-full">
        <div class="chamados-relatorio-card-header">
            <h2 class="chamados-relatorio-card-titulo">Evolução Mensal por Status (<?= $filtros['ano'] ?>)</h2>
            <div class="chamados-relatorio-card-acoes">
                <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="evolucaoChart">
                    <i class="fas fa-download"></i>
                </button>
                <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="evolucaoChart">
                    <i class="fas fa-expand-alt"></i>
                </button>
            </div>
        </div>
        <div class="chamados-relatorio-card-body">
            <div class="chamados-relatorio-grafico-container chamados-relatorio-grafico-container-lg">
                <canvas id="evolucaoChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Segunda linha de gráficos -->
    <div class="chamados-relatorio-graficos-grid">
        <!-- Chamados por Dia da Semana -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Chamados por Dia da Semana</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="diaSemanaChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="diaSemanaChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="diaSemanaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Taxa de Resolução -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Taxa de Resolução</h2>
                <div class="chamados-relatorio-card-badge">
                    <span class="chamados-relatorio-badge chamados-relatorio-badge-sucesso">
                        <?= $taxaResolucao['taxa_resolucao'] ?>% resolvidos
                    </span>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="taxaResolucaoChart"></canvas>
                </div>
            </div>
            <div class="chamados-relatorio-card-footer">
                <div class="chamados-relatorio-card-info">
                    <div class="chamados-relatorio-card-info-item">
                        <span class="chamados-relatorio-card-info-label">Total de chamados:</span>
                        <span class="chamados-relatorio-card-info-valor"><?= $taxaResolucao['total'] ?></span>
                    </div>
                    <div class="chamados-relatorio-card-info-item">
                        <span class="chamados-relatorio-card-info-label">Concluídos:</span>
                        <span class="chamados-relatorio-card-info-valor chamados-relatorio-card-info-valor-sucesso"><?= $taxaResolucao['concluidos'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira linha de gráficos -->
    <div class="chamados-relatorio-graficos-grid">
        <!-- Tempo Médio de Atendimento -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Tempo Médio de Atendimento</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="tempoChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="tempoChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="tempoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chamados por Setor -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Chamados por Setor</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="setorChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="setorChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="setorChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarta linha de gráficos -->
    <div class="chamados-relatorio-graficos-grid">
        <!-- Chamados por Tipo de Serviço -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Top 10 Tipos de Serviço</h2>
                <div class="chamados-relatorio-card-acoes">
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-download" data-chart="tipoChart">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chamados-relatorio-btn-icone chamados-relatorio-btn-expand" data-chart="tipoChart">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <div class="chamados-relatorio-card-body">
                <div class="chamados-relatorio-grafico-container">
                    <canvas id="tipoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabela de Chamados por Status -->
        <div class="chamados-relatorio-grafico-card">
            <div class="chamados-relatorio-card-header">
                <h2 class="chamados-relatorio-card-titulo">Dados: Chamados por Status</h2>
                <div class="chamados-relatorio-card-badge">
                    <span class="chamados-relatorio-badge chamados-relatorio-badge-primario">
                        <?= $estatisticasGerais['total'] ?> chamados
                    </span>
                </div>
            </div>
            <div class="chamados-relatorio-card-body chamados-relatorio-card-body-tabela">
                <div class="chamados-relatorio-tabela-container">
                    <table class="chamados-relatorio-tabela">
                        <thead>
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
                                $statusClass = 'secundario';
                                $statusNome = strtolower($row['status_nome'] ?? '');
                                if (strpos($statusNome, 'aberto') !== false) {
                                    $statusClass = 'perigo';
                                } elseif (strpos($statusNome, 'andamento') !== false || strpos($statusNome, 'atendimento') !== false) {
                                    $statusClass = 'alerta';
                                } elseif (strpos($statusNome, 'concluído') !== false || strpos($statusNome, 'resolvido') !== false) {
                                    $statusClass = 'sucesso';
                                } elseif (strpos($statusNome, 'cancelado') !== false) {
                                    $statusClass = 'escuro';
                                } elseif (strpos($statusNome, 'pendente') !== false || strpos($statusNome, 'pausado') !== false) {
                                    $statusClass = 'info';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <span class="chamados-relatorio-badge chamados-relatorio-badge-<?= $statusClass ?>">
                                            <?= htmlspecialchars($row['status_nome'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $row['total'] ?? 0 ?></td>
                                    <td class="text-center">
                                        <div class="chamados-relatorio-progresso">
                                            <div class="chamados-relatorio-progresso-barra chamados-relatorio-progresso-barra-<?= $statusClass ?>"
                                                style="width: <?= $percentual ?>%;">
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
                                    <td colspan="4" class="text-center">Nenhum dado encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualização expandida de gráficos -->
<div class="chamados-relatorio-modal" id="chartModal">
    <div class="chamados-relatorio-modal-conteudo">
        <div class="chamados-relatorio-modal-header">
            <h3 class="chamados-relatorio-modal-titulo" id="chartModalTitle">Visualização Expandida</h3>
            <button class="chamados-relatorio-modal-fechar" id="closeChartModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chamados-relatorio-modal-body">
            <div class="chamados-relatorio-modal-grafico-container">
                <canvas id="modalChart"></canvas>
            </div>
        </div>
        <div class="chamados-relatorio-modal-footer">
            <button class="chamados-relatorio-btn chamados-relatorio-btn-secundario" id="downloadModalChart">
                <i class="fas fa-download"></i> Baixar Imagem
            </button>
            <button class="chamados-relatorio-btn chamados-relatorio-btn-primario" id="closeModalBtn">
                <i class="fas fa-times"></i> Fechar
            </button>
        </div>
    </div>
</div>