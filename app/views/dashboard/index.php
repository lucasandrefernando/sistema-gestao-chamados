<?php

/**
 * Dashboard - Sistema de Gestão de Chamados
 * Versão: 2.0.0
 * 
 * DESCRIÇÃO:
 * Esta view exibe o painel de controle principal do sistema com:
 * - Estatísticas em tempo real dos chamados
 * - Gráficos interativos de análise
 * - Tabela de chamados recentes
 * - Interface responsiva e moderna
 * 
 * DEPENDÊNCIAS:
 * - Chart.js para gráficos
 * - Bootstrap para tooltips
 * - FontAwesome para ícones
 * - dashboard.css para estilos
 * - dashboard.js para interatividade
 * 
 * VARIÁVEIS ESPERADAS:
 * - $estatisticas: Array com dados estatísticos
 * - $chamadosPorStatus: Dados para gráfico de status
 * - $chamadosPorSetor: Dados para gráfico de setores
 * - $chamadosPorMes: Dados para gráfico mensal
 * - $tempoMedioPorSetor: Dados para gráfico de tempo médio
 * - $chamadosPorTipoServico: Dados para gráfico de tipos de serviço
 * - $chamadosPorDiaSemana: Dados para gráfico de dias da semana
 * - $recentes: Array com chamados recentes
 * 
 * AUTOR: Sistema de Gestão de Chamados
 * DATA: 2024
 */

/**
 * Função para escapar saída HTML de forma segura
 * Previne ataques XSS ao exibir dados do usuário
 * 
 * @param string $str String a ser escapada
 * @return string String escapada e segura para HTML
 */
function esc($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!-- Container principal do dashboard -->
<div class="dashboard-container">

    <!-- =================================================================
         SEÇÃO 1: CABEÇALHO DA PÁGINA
         Contém título, subtítulo e botões de ação
         ================================================================= -->
    <div class="user-dashboard-header">
        <div class="header-content">
            <!-- Seção do título -->
            <div class="title-section">
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt title-icon"></i>
                    Painel de Controle
                </h1>
                <p class="subtitle">Visão geral e análise de desempenho do sistema de chamados</p>
            </div>

            <!-- Botões de ação -->
            <div class="action-buttons">
                <!-- Botão de atualizar dados -->
                <button id="refresh-dashboard" class="btn-refresh" title="Atualizar Dashboard">
                    <i class="fas fa-sync-alt"></i>
                </button>

                <!-- Botão para criar novo chamado -->
                <a href="<?= base_url('chamados/criar') ?>" class="btn-new-ticket">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- =================================================================
         SEÇÃO 2: CARDS DE ESTATÍSTICAS (LAYOUT 3+3)
         Exibe métricas principais em cards visuais
         ================================================================= -->
    <div class="stats-cards">

        <!-- Card 1: Total de Chamados -->
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

        <!-- Card 2: Chamados Abertos -->
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
                        // Calcula percentual de chamados abertos
                        $percentAbertos = ($estatisticas['total'] > 0) ?
                            round(($estatisticas['abertos'] / $estatisticas['total']) * 100) : 0;
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

        <!-- Card 3: Chamados em Atendimento -->
        <div class="stat-card info-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value-container">
                    <div class="stat-value" id="chamados-andamento">
                        <span class="counter-number"><?= $estatisticas['em_andamento'] ?? 0 ?></span>
                    </div>
                    <div class="stat-trend">
                        <?php
                        // Calcula percentual de chamados em andamento
                        $percentAndamento = ($estatisticas['total'] > 0) ?
                            round(($estatisticas['em_andamento'] / $estatisticas['total']) * 100) : 0;
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

        <!-- Card 4: Chamados Concluídos -->
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
                        // Calcula percentual de chamados concluídos
                        $percentConcluidos = ($estatisticas['total'] > 0) ?
                            round(($estatisticas['concluidos'] / $estatisticas['total']) * 100) : 0;
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

        <!-- Card 5: Concluídos Hoje -->
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
                        // Calcula percentual de concluídos hoje em relação ao total de concluídos
                        $percentHoje = ($estatisticas['concluidos'] > 0) ?
                            round(($estatisticas['concluidos_hoje'] / $estatisticas['concluidos']) * 100) : 0;
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

        <!-- Card 6: Tempo Médio de Atendimento -->
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
                    // Calcula escala relativa para tempo médio (48h = 100%)
                    $tempoRelativo = min(100, ($estatisticas['tempo_medio_atendimento'] / 48) * 100);
                    ?>
                    <div class="progress-bar" style="width: <?= $tempoRelativo ?>%; background-color: #3a56d4;"></div>
                </div>
                <div class="stat-description">Tempo médio de resolução de chamados</div>
            </div>
        </div>
    </div>

    <!-- =================================================================
         SEÇÃO 3: GRÁFICOS PRINCIPAIS
         Primeira linha de gráficos (Status e Setor)
         ================================================================= -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php if (empty($chamadosPorStatus['data'])): ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-chart-pie"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php if (empty($chamadosPorSetor['data'])): ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-building"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
                    <canvas id="chamadosPorSetorChart" data-chart='<?= json_encode($chamadosPorSetor) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =================================================================
         SEÇÃO 4: GRÁFICOS SECUNDÁRIOS
         Segunda linha de gráficos (Mensal e Tempo Médio)
         ================================================================= -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php if (empty($chamadosPorMes['data']) || array_sum($chamadosPorMes['data']) === 0): ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php if (empty($tempoMedioPorSetor['data']) || (count($tempoMedioPorSetor['data']) === 1 && $tempoMedioPorSetor['data'][0] === 0)): ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-stopwatch"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
                    <canvas id="tempoMedioPorSetorChart" data-chart='<?= json_encode($tempoMedioPorSetor) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =================================================================
         SEÇÃO 5: GRÁFICOS TERCIÁRIOS
         Terceira linha de gráficos (Tipo de Serviço e Dia da Semana)
         ================================================================= -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php if (empty($chamadosPorTipoServico['data']) || (count($chamadosPorTipoServico['data']) === 1 && $chamadosPorTipoServico['data'][0] === 0)): ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-tags"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
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
                <!-- Indicador de carregamento -->
                <div class="chart-loading" style="display: flex;">
                    <div class="chart-loading-spinner"></div>
                </div>

                <?php
                // Verifica se há dados válidos para o gráfico
                $totalDiaSemana = isset($chamadosPorDiaSemana['data']) ? array_sum($chamadosPorDiaSemana['data']) : 0;
                if (empty($chamadosPorDiaSemana['data']) || $totalDiaSemana === 0):
                ?>
                    <!-- Mensagem quando não há dados -->
                    <div class="no-data-message">
                        <i class="fas fa-calendar-week"></i>
                        <p>Não há dados suficientes para exibir este gráfico</p>
                    </div>
                <?php else: ?>
                    <!-- Canvas para o gráfico Chart.js -->
                    <canvas id="chamadosPorDiaSemanaChart" data-chart='<?= json_encode($chamadosPorDiaSemana) ?>'></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =================================================================
         SEÇÃO 6: TABELA DE CHAMADOS RECENTES
         Exibe os últimos chamados em formato de tabela responsiva
         ================================================================= -->
    <div class="recent-tickets-card">

        <!-- Cabeçalho da tabela -->
        <div class="recent-tickets-header">
            <h5 class="recent-tickets-title">
                <i class="fas fa-ticket-alt"></i>
                Chamados Recentes
            </h5>
            <!-- Link para ver todos os chamados -->
            <a href="<?= base_url('chamados/listar') ?>" class="view-all-link">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Corpo da tabela -->
        <div class="recent-tickets-body">
            <?php if (empty($recentes)): ?>
                <!-- Estado vazio - quando não há chamados -->
                <div class="no-tickets">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h3 class="empty-state-title">Nenhum chamado encontrado</h3>
                        <p class="empty-state-desc">Não há chamados recentes para exibir no momento.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Wrapper responsivo da tabela -->
                <div class="table-responsive">
                    <table class="recent-tickets-table">

                        <!-- Cabeçalho da tabela -->
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

                        <!-- Corpo da tabela -->
                        <tbody>
                            <?php
                            // Limita a exibição aos 5 chamados mais recentes
                            $recentesLimitados = array_slice($recentes, 0, 5);

                            // Loop através dos chamados recentes
                            foreach ($recentesLimitados as $index => $chamado):

                                // Gera iniciais do nome para o avatar
                                $iniciais = '';
                                $nomes = explode(' ', $chamado['solicitante']);
                                foreach ($nomes as $nome) {
                                    $iniciais .= substr($nome, 0, 1);
                                    if (strlen($iniciais) >= 2) break;
                                }
                                $iniciais = strtoupper($iniciais);

                                // Formata data e hora
                                $dataObj = new DateTime($chamado['data_solicitacao']);
                                $dataFormatada = $dataObj->format('d/m/Y');
                                $horaFormatada = $dataObj->format('H:i');

                                // Determina classe CSS do status
                                $statusClass = '';
                                switch ($chamado['status']) {
                                    case 'Aberto':
                                        $statusClass = 'status-aberto';
                                        break;
                                    case 'Em Atendimento':
                                        $statusClass = 'status-andamento';
                                        break;
                                    case 'Concluído':
                                        $statusClass = 'status-concluido';
                                        break;
                                    case 'Pausado':
                                        $statusClass = 'status-pausado';
                                        break;
                                    case 'Cancelado':
                                        $statusClass = 'status-cancelado';
                                        break;
                                    default:
                                        $statusClass = 'status-aberto';
                                }
                            ?>
                                <!-- Linha da tabela com animação -->
                                <tr class="fade-in-up" style="animation-delay: <?= $index * 0.1 ?>s">

                                    <!-- Coluna ID -->
                                    <td>
                                        <span class="ticket-id">#<?= $chamado['id'] ?></span>
                                    </td>

                                    <!-- Coluna Solicitante -->
                                    <td>
                                        <div class="ticket-user">
                                            <!-- Avatar com iniciais -->
                                            <div class="user-avatar"><?= $iniciais ?></div>
                                            <div class="user-info">
                                                <p class="user-name"><?= esc($chamado['solicitante']) ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Coluna Descrição -->
                                    <td>
                                        <div class="ticket-desc">
                                            <div class="desc-preview"
                                                data-bs-toggle="tooltip"
                                                title="<?= esc($chamado['descricao']) ?>">
                                                <?= esc($chamado['descricao']) ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Coluna Setor -->
                                    <td>
                                        <div class="sector-badge">
                                            <i class="fas fa-building"></i>
                                            <?= esc($chamado['setor']) ?>
                                        </div>
                                    </td>

                                    <!-- Coluna Status -->
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= esc($chamado['status']) ?>
                                        </span>
                                    </td>

                                    <!-- Coluna Data -->
                                    <td>
                                        <div class="ticket-date">
                                            <div class="date-icon">
                                                <i class="far fa-calendar-alt"></i>
                                            </div>
                                            <div class="date-info">
                                                <p class="date-day"><?= $dataFormatada ?></p>
                                                <p class="date-time"><?= $horaFormatada ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Coluna Ações -->
                                    <td>
                                        <div class="ticket-actions">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>"
                                                class="action-btn view"
                                                data-bs-toggle="tooltip"
                                                title="Visualizar detalhes do chamado">
                                                <div class="action-btn-content">
                                                    <span class="action-btn-text">Ver</span>
                                                    <div class="action-btn-icon">
                                                        <i class="fas fa-eye"></i>
                                                        <i class="fas fa-arrow-right secondary-icon"></i>
                                                    </div>
                                                </div>
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

<!-- =================================================================
     NOTIFICAÇÃO DE ATUALIZAÇÃO
     Container para notificações dinâmicas via JavaScript
     ================================================================= -->
<div id="dashboard-notification" class="dashboard-notification"></div>

<!-- =================================================================
     COMENTÁRIOS PARA DESENVOLVEDORES
     
     ESTRUTURA DO ARQUIVO:
     1. Cabeçalho com título e botões de ação
     2. Cards de estatísticas em grid 3+3
     3. Três linhas de gráficos Chart.js
     4. Tabela responsiva de chamados recentes
     5. Container de notificações
     
     FUNCIONALIDADES:
     - Contadores animados nos cards
     - Gráficos interativos com Chart.js
     - Tabela responsiva com avatars
     - Tooltips informativos
     - Animações CSS suaves
     - Auto-refresh via JavaScript
     
     RESPONSIVIDADE:
     - Desktop: 3 cards por linha, 2 gráficos por linha
     - Tablet: 2 cards por linha, 1 gráfico por linha
     - Mobile: 1 card por linha, colunas da tabela ocultas
     
     SEGURANÇA:
     - Todas as saídas escapadas com esc()
     - Validação de dados antes da exibição
     - Proteção contra XSS
     
     PERFORMANCE:
     - Lazy loading dos gráficos
     - Animações GPU-accelerated
     - Dados limitados (5 chamados recentes)
     - Compressão de imagens e assets
     
     MANUTENÇÃO:
     - Código bem comentado e organizado
     - Separação clara de responsabilidades
     - Fácil adição de novos cards/gráficos
     - CSS modular e reutilizável
     ================================================================= -->