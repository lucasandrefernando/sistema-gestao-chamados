<div class="setor-header animate-fade-in">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="setor-title">
                <i class="fas fa-building"></i>
                <?= htmlspecialchars($setor['nome']) ?>
                <span class="setor-badge">
                    <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                </span>
            </h1>
            <p class="text-white-50 mb-0">
                <?php if (!empty($setor['descricao'])): ?>
                    <?= nl2br(htmlspecialchars(substr($setor['descricao'], 0, 120))) ?>
                    <?= strlen($setor['descricao']) > 120 ? '...' : '' ?>
                <?php else: ?>
                    <em>Sem descrição</em>
                <?php endif; ?>
            </p>
        </div>
        <div class="setor-actions">
            <a href="<?= base_url('setores/visualizacao') ?>" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <?php if (is_admin()): ?>
                <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="btn btn-primary ms-2">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Navegação por abas -->
<ul class="nav nav-tabs-custom animate-fade-in" id="setorTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">
            <i class="fas fa-chart-line"></i> Dashboard
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="chamados-tab" data-bs-toggle="tab" data-bs-target="#chamados" type="button" role="tab" aria-controls="chamados" aria-selected="false">
            <i class="fas fa-ticket-alt"></i> Chamados
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab" aria-controls="usuarios" aria-selected="false">
            <i class="fas fa-users"></i> Usuários
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="false">
            <i class="fas fa-info-circle"></i> Informações
        </button>
    </li>
</ul>

<!-- Conteúdo das abas -->
<div class="tab-content animate-fade-in" id="setorTabsContent">
    <!-- Aba Dashboard -->
    <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
        <!-- Cards de Estatísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon" style="background-color: var(--danger-color);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-card-info">
                        <h5 class="stat-card-title">
                            <?php
                            $chamadosAbertos = 0;
                            if (!empty($estatisticas['chamados_por_status'])) {
                                foreach ($estatisticas['chamados_por_status'] as $status) {
                                    if ((isset($status['status']) && $status['status'] == 'aberto') ||
                                        (isset($status['status_id']) && $status['status_id'] == 1) ||
                                        (isset($status['nome']) && strtolower($status['nome']) == 'aberto')
                                    ) {
                                        $chamadosAbertos = $status['total'];
                                        break;
                                    }
                                }
                            }
                            echo $chamadosAbertos;
                            ?>
                        </h5>
                        <p class="stat-card-text">Chamados Abertos</p>
                        <?php
                        $percentAbertos = $estatisticas['total_chamados'] > 0 ?
                            ($chamadosAbertos / $estatisticas['total_chamados']) * 100 : 0;
                        ?>
                        <div class="progress stat-card-progress">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $percentAbertos ?>%" aria-valuenow="<?= $percentAbertos ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon" style="background-color: var(--warning-color);">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-card-info">
                        <h5 class="stat-card-title">
                            <?php
                            $chamadosEmAndamento = 0;
                            if (!empty($estatisticas['chamados_por_status'])) {
                                foreach ($estatisticas['chamados_por_status'] as $status) {
                                    if ((isset($status['status']) && $status['status'] == 'em_andamento') ||
                                        (isset($status['status_id']) && $status['status_id'] == 2) ||
                                        (isset($status['nome']) && strtolower($status['nome']) == 'em atendimento')
                                    ) {
                                        $chamadosEmAndamento = $status['total'];
                                        break;
                                    }
                                }
                            }
                            echo $chamadosEmAndamento;
                            ?>
                        </h5>
                        <p class="stat-card-text">Em Atendimento</p>
                        <?php
                        $percentEmAndamento = $estatisticas['total_chamados'] > 0 ?
                            ($chamadosEmAndamento / $estatisticas['total_chamados']) * 100 : 0;
                        ?>
                        <div class="progress stat-card-progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $percentEmAndamento ?>%" aria-valuenow="<?= $percentEmAndamento ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon" style="background-color: var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card-info">
                        <h5 class="stat-card-title">
                            <?php
                            $chamadosConcluidos = 0;
                            if (!empty($estatisticas['chamados_por_status'])) {
                                foreach ($estatisticas['chamados_por_status'] as $status) {
                                    if ((isset($status['status']) && $status['status'] == 'concluido') ||
                                        (isset($status['status_id']) && $status['status_id'] == 4) ||
                                        (isset($status['nome']) && strtolower($status['nome']) == 'concluído')
                                    ) {
                                        $chamadosConcluidos = $status['total'];
                                        break;
                                    }
                                }
                            }
                            echo $chamadosConcluidos;
                            ?>
                        </h5>
                        <p class="stat-card-text">Concluídos</p>
                        <?php
                        $percentConcluidos = $estatisticas['total_chamados'] > 0 ?
                            ($chamadosConcluidos / $estatisticas['total_chamados']) * 100 : 0;
                        ?>
                        <div class="progress stat-card-progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentConcluidos ?>%" aria-valuenow="<?= $percentConcluidos ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-card-icon" style="background-color: var(--info-color);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-card-info">
                        <h5 class="stat-card-title"><?= $estatisticas['total_chamados'] ?></h5>
                        <p class="stat-card-text">Total de Chamados</p>
                        <div class="progress stat-card-progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($estatisticas['tempo_medio_atendimento'])): ?>
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-card-icon" style="background-color: var(--primary-color);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-card-info">
                            <h5 class="stat-card-title"><?= formatarTempo($estatisticas['tempo_medio_atendimento']) ?></h5>
                            <p class="stat-card-text">Tempo Médio de Atendimento</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($statusMaisComum): ?>
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-card-icon" style="background-color: var(--secondary-color);">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-card-info">
                            <h5 class="stat-card-title"><?= isset($statusMaisComum['nome']) ? $statusMaisComum['nome'] : formatarStatus($statusMaisComum['status']) ?></h5>
                            <p class="stat-card-text">Status Mais Comum</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Gráfico de Status -->
            <div class="col-md-6 mb-4">
                <div class="content-card h-100">
                    <div class="content-card-header">
                        <h5><i class="fas fa-chart-pie"></i> Chamados por Status</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (!empty($estatisticas['chamados_por_status'])): ?>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <?php foreach ($estatisticas['chamados_por_status'] as $status): ?>
                                    <?php
                                    $statusKey = isset($status['status']) ? $status['status'] : (isset($status['status_id']) ? $status['status_id'] :
                                        strtolower(str_replace(' ', '_', $status['nome'] ?? '')));
                                    $statusName = isset($status['nome']) ? $status['nome'] : (isset($status['status']) ? formatarStatus($status['status']) :
                                        formatarStatusById($status['status_id'] ?? 0));
                                    ?>
                                    <div class="legend-item">
                                        <span class="legend-color bg-<?= getStatusColor($statusKey) ?>"></span>
                                        <span><?= $statusName ?> (<?= $status['total'] ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Sem dados para exibir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Prioridade -->
            <div class="col-md-6 mb-4">
                <div class="content-card h-100">
                    <div class="content-card-header">
                        <h5><i class="fas fa-tags"></i> Chamados por Tipo de Serviço</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (!empty($estatisticas['chamados_por_prioridade'])): ?>
                            <div class="chart-container">
                                <canvas id="priorityChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <?php
                                $colors = [
                                    'rgba(40, 167, 69, 0.7)',
                                    'rgba(255, 193, 7, 0.7)',
                                    'rgba(220, 53, 69, 0.7)',
                                    'rgba(108, 17, 25, 0.7)',
                                    'rgba(0, 123, 255, 0.7)',
                                    'rgba(111, 66, 193, 0.7)'
                                ];
                                foreach ($estatisticas['chamados_por_prioridade'] as $index => $prioridade):
                                    $colorIndex = $index % count($colors);
                                ?>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: <?= $colors[$colorIndex] ?>"></span>
                                        <span><?= isset($prioridade['nome']) ? $prioridade['nome'] : formatarPrioridade($prioridade['prioridade']) ?> (<?= $prioridade['total'] ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Sem dados para exibir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Chamados por Mês -->
            <div class="col-md-8 mb-4">
                <div class="content-card h-100">
                    <div class="content-card-header">
                        <h5><i class="fas fa-chart-bar"></i> Chamados por Mês</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (!empty($estatisticas['chamados_por_mes'])): ?>
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Sem dados para exibir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Usuários Mais Ativos -->
            <div class="col-md-4 mb-4">
                <div class="content-card h-100">
                    <div class="content-card-header">
                        <h5><i class="fas fa-users"></i> Solicitantes Mais Ativos</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (!empty($estatisticas['usuarios_mais_ativos'])): ?>
                            <ul class="user-list list-unstyled">
                                <?php foreach ($estatisticas['usuarios_mais_ativos'] as $index => $usuario): ?>
                                    <li class="user-list-item">
                                        <div class="user-info">
                                            <div class="user-avatar" data-name="<?= htmlspecialchars($usuario['nome']) ?>">
                                                <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                            </div>
                                            <span class="user-name"><?= htmlspecialchars($usuario['nome']) ?></span>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?= $usuario['total'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Sem dados para exibir</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aba Chamados -->
    <div class="tab-pane fade" id="chamados" role="tabpanel" aria-labelledby="chamados-tab">
        <div class="content-card">
            <div class="content-card-header">
                <h5><i class="fas fa-ticket-alt"></i> Chamados do Setor</h5>
                <div class="filters-container">
                    <div class="filter-item">
                        <label class="filter-switch">
                            <input type="checkbox" id="mostrarTodosChamados">
                            <span class="filter-slider"></span>
                        </label>
                        <span class="filter-label">Mostrar todos</span>
                    </div>
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="chamadosSearch" class="form-control" placeholder="Buscar chamados...">
                    </div>
                </div>
            </div>
            <div class="content-card-body p-0">
                <?php if (!empty($chamados)): ?>
                    <div class="chamados-table-container">
                        <table class="table chamados-table" id="chamadosTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Descrição</th>
                                    <th>Solicitante</th>
                                    <th>Status</th>
                                    <th>Data de Solicitação</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chamados as $chamado): ?>
                                    <tr class="chamado-row" data-status="<?= isset($chamado['status_id']) ? $chamado['status_id'] : (isset($chamado['status']) ? $chamado['status'] : '') ?>">
                                        <td><?= isset($chamado['id']) ? $chamado['id'] : 'N/A' ?></td>
                                        <td class="text-truncate" style="max-width: 250px;" title="<?= isset($chamado['titulo']) ? htmlspecialchars($chamado['titulo']) : (isset($chamado['descricao']) ? htmlspecialchars($chamado['descricao']) : 'N/A') ?>">
                                            <?= isset($chamado['titulo']) ? htmlspecialchars($chamado['titulo']) : (isset($chamado['descricao']) ? htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') : 'N/A') ?>
                                        </td>
                                        <td><?= isset($chamado['solicitante_nome']) ? htmlspecialchars($chamado['solicitante_nome']) : (isset($chamado['solicitante']) ? htmlspecialchars($chamado['solicitante']) : 'N/A') ?></td>
                                        <td>
                                            <?php if (isset($chamado['status'])): ?>
                                                <span class="badge badge-<?= $chamado['status'] ?>">
                                                    <?= formatarStatus($chamado['status']) ?>
                                                </span>
                                            <?php elseif (isset($chamado['status_nome'])): ?>
                                                <span class="badge badge-<?= strtolower(str_replace(' ', '_', $chamado['status_nome'])) ?>">
                                                    <?= $chamado['status_nome'] ?>
                                                </span>
                                            <?php elseif (isset($chamado['status_id'])): ?>
                                                <span class="badge badge-<?= getStatusColorById($chamado['status_id']) ?>">
                                                    <?= formatarStatusById($chamado['status_id']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Desconhecido</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= isset($chamado['data_abertura']) ? formatarData($chamado['data_abertura']) : (isset($chamado['data_solicitacao']) ? formatarData($chamado['data_solicitacao']) : 'N/A') ?></td>
                                        <td class="text-center">
                                            <?php if (isset($chamado['id'])): ?>
                                                <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-primary btn-action">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-action" disabled>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noResults" class="text-center py-5" style="display: none;">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum chamado encontrado para a busca.</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum chamado encontrado para este setor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Aba Usuários -->
    <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
        <div class="content-card">
            <div class="content-card-header">
                <h5><i class="fas fa-users"></i> Usuários do Setor</h5>
            </div>
            <div class="content-card-body">
                <?php if (!empty($estatisticas['usuarios_mais_ativos'])): ?>
                    <div class="row">
                        <?php foreach ($estatisticas['usuarios_mais_ativos'] as $usuario): ?>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="user-avatar me-3" data-name="<?= htmlspecialchars($usuario['nome']) ?>">
                                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h6>
                                            <p class="text-muted mb-0 small">
                                                <i class="fas fa-ticket-alt me-1"></i> <?= $usuario['total'] ?> chamados
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum usuário encontrado para este setor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Aba Informações -->
    <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
        <div class="content-card">
            <div class="content-card-header">
                <h5><i class="fas fa-info-circle"></i> Informações do Setor</h5>
            </div>
            <div class="content-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-primary">Descrição</h6>
                        <?php if (!empty($setor['descricao'])): ?>
                            <p><?= nl2br(htmlspecialchars($setor['descricao'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted"><em>Sem descrição</em></p>
                        <?php endif; ?>

                        <h6 class="mb-3 mt-4 text-primary">Detalhes</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px;">ID do Setor:</th>
                                        <td><?= $setor['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?>">
                                                <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Data de Criação:</th>
                                        <td><?= isset($setor['criado_em']) ? formatarData($setor['criado_em']) : (isset($setor['data_criacao']) ? formatarData($setor['data_criacao']) : 'N/A') ?></td>
                                    </tr>
                                    <?php if (isset($setor['atualizado_em']) || isset($setor['data_atualizacao'])): ?>
                                        <tr>
                                            <th>Última Atualização:</th>
                                            <td><?= isset($setor['atualizado_em']) ? formatarData($setor['atualizado_em']) : formatarData($setor['data_atualizacao']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3 text-primary">Estatísticas</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;">Total de Chamados:</th>
                                        <td><?= $estatisticas['total_chamados'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Chamados Abertos:</th>
                                        <td><?= $chamadosAbertos ?></td>
                                    </tr>
                                    <tr>
                                        <th>Chamados em Atendimento:</th>
                                        <td><?= $chamadosEmAndamento ?></td>
                                    </tr>
                                    <tr>
                                        <th>Chamados Concluídos:</th>
                                        <td><?= $chamadosConcluidos ?></td>
                                    </tr>
                                    <?php if (!empty($estatisticas['tempo_medio_atendimento'])): ?>
                                        <tr>
                                            <th>Tempo Médio de Atendimento:</th>
                                            <td><?= formatarTempo($estatisticas['tempo_medio_atendimento']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($mesMaisChamados): ?>
                                        <tr>
                                            <th>Mês Mais Ativo:</th>
                                            <td><?= $mesMaisChamados['mes_ano'] ?> (<?= $mesMaisChamados['total'] ?> chamados)</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para dados dos gráficos -->
<script id="monthlyChartData" type="application/json">
    {
        "labels": [
            <?php if (!empty($estatisticas['chamados_por_mes'])): ?>
                <?php foreach ($estatisticas['chamados_por_mes'] as $index => $mes): ?> "<?= $mes['mes_ano'] ?>"
                    <?= $index < count($estatisticas['chamados_por_mes']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ],
        "data": [
            <?php if (!empty($estatisticas['chamados_por_mes'])): ?>
                <?php foreach ($estatisticas['chamados_por_mes'] as $index => $mes): ?>
                    <?= $mes['total'] ?><?= $index < count($estatisticas['chamados_por_mes']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ]
    }
</script>

<script id="statusChartData" type="application/json">
    {
        "labels": [
            <?php if (!empty($estatisticas['chamados_por_status'])): ?>
                <?php foreach ($estatisticas['chamados_por_status'] as $index => $status): ?>
                    <?php
                    $statusName = isset($status['nome']) ? $status['nome'] : (isset($status['status']) ? formatarStatus($status['status']) :
                        formatarStatusById($status['status_id'] ?? 0));
                    ?> "<?= $statusName ?>"
                    <?= $index < count($estatisticas['chamados_por_status']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ],
        "data": [
            <?php if (!empty($estatisticas['chamados_por_status'])): ?>
                <?php foreach ($estatisticas['chamados_por_status'] as $index => $status): ?>
                    <?= $status['total'] ?><?= $index < count($estatisticas['chamados_por_status']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ]
    }
</script>

<script id="priorityChartData" type="application/json">
    {
        "labels": [
            <?php if (!empty($estatisticas['chamados_por_prioridade'])): ?>
                <?php foreach ($estatisticas['chamados_por_prioridade'] as $index => $prioridade): ?> "<?= isset($prioridade['nome']) ? $prioridade['nome'] : formatarPrioridade($prioridade['prioridade']) ?>"
                    <?= $index < count($estatisticas['chamados_por_prioridade']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ],
        "data": [
            <?php if (!empty($estatisticas['chamados_por_prioridade'])): ?>
                <?php foreach ($estatisticas['chamados_por_prioridade'] as $index => $prioridade): ?>
                    <?= $prioridade['total'] ?><?= $index < count($estatisticas['chamados_por_prioridade']) - 1 ? ',' : '' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        ]
    }
</script>

<!-- Funções auxiliares para formatação de status por ID -->
<?php if (!function_exists('getStatusColorById')): ?>
    <script>
        function getStatusColorById(statusId) {
            const colors = {
                1: 'danger', // Aberto
                2: 'warning', // Em Atendimento
                3: 'info', // Pausado
                4: 'success', // Concluído
                5: 'secondary' // Cancelado
            };
            return colors[statusId] || 'primary';
        }

        function formatarStatusById(statusId) {
            const formatado = {
                1: 'Aberto',
                2: 'Em Atendimento',
                3: 'Pausado',
                4: 'Concluído',
                5: 'Cancelado'
            };
            return formatado[statusId] || 'Desconhecido';
        }
    </script>
<?php endif; ?>