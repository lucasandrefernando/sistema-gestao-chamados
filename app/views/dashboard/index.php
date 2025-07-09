<?php

/**
 * Dashboard - Sistema de Gestão de Chamados
 * 
 * Esta view exibe o painel de controle com estatísticas e gráficos sobre os chamados
 * do sistema, permitindo ao usuário visualizar o desempenho e status dos chamados.
 */

/**
 * Função para escapar saída HTML de forma segura
 * 
 * @param string $str String a ser escapada
 * @return string String escapada
 */
function esc($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<div class="dashboard-container">
    <!-- Cabeçalho da Página -->
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
                <button id="refresh-dashboard" class="btn-refresh" title="Atualizar Dashboard">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="<?= base_url('chamados/criar') ?>" class="btn-new-ticket">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-cards">
        <!-- Card: Total de Chamados -->
        <div class="stat-card total-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="total-chamados">
                        <span class="counter-number"><?= $estatisticas['total'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-label">Total de Chamados</div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%; background-color: #4361ee;"></div>
                </div>
                <div class="stat-description">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <!-- Card: Chamados Abertos -->
        <div class="stat-card warning-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="chamados-abertos">
                        <span class="counter-number"><?= $estatisticas['abertos'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <?php
                        $percentAbertos = ($estatisticas['total'] > 0) ? round(($estatisticas['abertos'] / $estatisticas['total']) * 100) : 0;
                        ?>
                        <span><?= $percentAbertos ?>%</span>
                    </div>
                </div>
                <div class="stat-label">Chamados Abertos</div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $percentAbertos ?>%; background-color: #f39c12;"></div>
                </div>
                <div class="stat-description">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <!-- Card: Chamados em Atendimento (COM ESTILO INLINE) -->
        <div class="stat-card info-card" style="display: flex !important; flex-direction: row !important;">
            <div class="stat-icon" style="margin-right: 0.75rem !important;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content" style="flex: 1 !important; width: auto !important;">
                <div class="stat-value-container" style="display: flex !important; justify-content: space-between !important;">
                    <div class="stat-value" id="chamados-andamento">
                        <span class="counter-number"><?= $estatisticas['em_andamento'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <?php
                        $percentAndamento = ($estatisticas['total'] > 0) ? round(($estatisticas['em_andamento'] / $estatisticas['total']) * 100) : 0;
                        ?>
                        <span><?= $percentAndamento ?>%</span>
                    </div>
                </div>
                <div class="stat-label">Em Atendimento</div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $percentAndamento ?>%; background-color: #3498db;"></div>
                </div>
                <div class="stat-description">Chamados que estão sendo processados</div>
            </div>
        </div>
        <!-- Card: Chamados Concluídos -->
        <div class="stat-card success-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="chamados-concluidos">
                        <span class="counter-number"><?= $estatisticas['concluidos'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <?php
                        $percentConcluidos = ($estatisticas['total'] > 0) ? round(($estatisticas['concluidos'] / $estatisticas['total']) * 100) : 0;
                        ?>
                        <span><?= $percentConcluidos ?>%</span>
                    </div>
                </div>
                <div class="stat-label">Concluídos</div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $percentConcluidos ?>%; background-color: #2ecc71;"></div>
                </div>
                <div class="stat-description">Chamados finalizados com sucesso</div>
            </div>
        </div>

        <!-- Card: Concluídos Hoje -->
        <div class="stat-card success-light-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="concluidos-hoje">
                        <span class="counter-number"><?= $estatisticas['concluidos_hoje'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <?php
                        $percentHoje = ($estatisticas['concluidos'] > 0) ? round(($estatisticas['concluidos_hoje'] / $estatisticas['concluidos']) * 100) : 0;
                        ?>
                        <span><?= $percentHoje ?>%</span>
                    </div>
                </div>
                <div class="stat-label">Concluídos Hoje</div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $percentHoje ?>%; background-color: #27ae60;"></div>
                </div>
                <div class="stat-description">Chamados finalizados nas últimas 24h</div>
            </div>
        </div>

        <!-- Card: Tempo Médio -->
        <div class="stat-card primary-light-card">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="tempo-medio">
                        <span class="counter-number"><?= $estatisticas['tempo_medio_atendimento'] ?? 0 ?></span>
                        <span class="counter-unit">h</span>
                    </div>
                    <div class="stat-trend">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-label">Tempo Médio</div>
                <div class="stat-progress">
                    <?php
                    // Calculamos uma escala relativa para o tempo médio (considerando 48h como 100%)
                    $tempoRelativo = min(100, ($estatisticas['tempo_medio_atendimento'] / 48) * 100);
                    ?>
                    <div class="progress-bar" style="width: <?= $tempoRelativo ?>%; background-color: #3a56d4;"></div>
                </div>
                <div class="stat-description">Tempo médio de resolução de chamados</div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principais -->
    <div class="charts-row">
        <!-- Gráfico: Chamados por Status -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-chart-pie"></i>
                    Chamados por Status
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php if (empty($chamadosPorStatus['data'])): ?>
                    <div class="no-data-message">
                        <i class="fas fa-chart-pie"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="chamadosPorStatusChart" data-chart='<?= json_encode($chamadosPorStatus) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico: Chamados por Setor -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-building"></i>
                    Chamados por Setor
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php if (empty($chamadosPorSetor['data'])): ?>
                    <div class="no-data-message">
                        <i class="fas fa-building"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="chamadosPorSetorChart" data-chart='<?= json_encode($chamadosPorSetor) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos Secundários -->
    <div class="charts-row">
        <!-- Gráfico: Chamados por Mês -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-calendar-alt"></i>
                    Chamados por Mês
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php if (empty($chamadosPorMes['data']) || array_sum($chamadosPorMes['data']) === 0): ?>
                    <div class="no-data-message">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="chamadosPorMesChart" data-chart='<?= json_encode($chamadosPorMes) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico: Tempo Médio por Setor -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-stopwatch"></i>
                    Tempo Médio por Setor (horas)
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php if (empty($tempoMedioPorSetor['data']) || (count($tempoMedioPorSetor['data']) === 1 && $tempoMedioPorSetor['data'][0] === 0)): ?>
                    <div class="no-data-message">
                        <i class="fas fa-stopwatch"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="tempoMedioPorSetorChart" data-chart='<?= json_encode($tempoMedioPorSetor) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos Terciários -->
    <div class="charts-row">
        <!-- Gráfico: Chamados por Tipo de Serviço -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-tags"></i>
                    Chamados por Tipo de Serviço
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php if (empty($chamadosPorTipoServico['data']) || (count($chamadosPorTipoServico['data']) === 1 && $chamadosPorTipoServico['data'][0] === 0)): ?>
                    <div class="no-data-message">
                        <i class="fas fa-tags"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="chamadosPorTipoServicoChart" data-chart='<?= json_encode($chamadosPorTipoServico) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico: Chamados por Dia da Semana -->
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="fas fa-calendar-week"></i>
                    Chamados por Dia da Semana
                </h5>
            </div>
            <div class="chart-body">
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>
                <?php
                $totalDiaSemana = isset($chamadosPorDiaSemana['data']) ? array_sum($chamadosPorDiaSemana['data']) : 0;
                if (empty($chamadosPorDiaSemana['data']) || $totalDiaSemana === 0):
                ?>
                    <div class="no-data-message">
                        <i class="fas fa-calendar-week"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <canvas id="chamadosPorDiaSemanaChart" data-chart='<?= json_encode($chamadosPorDiaSemana) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chamados Recentes -->
    <div class="recent-tickets-card">
        <div class="recent-tickets-header">
            <h5 class="recent-tickets-title">
                <i class="fas fa-ticket-alt"></i>
                Chamados Recentes
            </h5>
            <a href="<?= base_url('chamados/listar') ?>" class="view-all-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="recent-tickets-body">
            <?php if (empty($recentes)): ?>
                <div class="no-tickets">
                    <div class="no-data-message">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Nenhum chamado recente encontrado.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="recent-tickets-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Descrição</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Limita a 4 chamados
                            $recentesLimitados = array_slice($recentes, 0, 4);
                            foreach ($recentesLimitados as $index => $chamado):
                            ?>
                                <tr class="fade-in-up" style="animation-delay: <?= $index * 0.1 ?>s">
                                    <td class="ticket-id"><?= $chamado['id'] ?></td>
                                    <td class="ticket-user">
                                        <i class="fas fa-user"></i>
                                        <?= esc($chamado['solicitante']) ?>
                                    </td>
                                    <td>
                                        <div class="ticket-desc" data-bs-toggle="tooltip" title="<?= esc($chamado['descricao']) ?>">
                                            <?= esc(substr($chamado['descricao'], 0, 30)) ?><?= strlen($chamado['descricao']) > 30 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td><?= esc($chamado['setor']) ?></td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?= $chamado['status_cor'] ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= esc($chamado['status']) ?>
                                        </span>
                                    </td>
                                    <td class="ticket-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])) ?>
                                    </td>
                                    <td>
                                        <div class="ticket-actions">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="action-btn view" data-bs-toggle="tooltip" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notificação de atualização (será adicionada via JavaScript) -->
<div id="dashboard-notification" class="dashboard-notification"></div>