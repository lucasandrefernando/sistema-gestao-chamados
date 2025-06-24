<div class="setores-dashboard">
    <!-- Cabeçalho com título e ações principais -->
    <div class="dashboard-header">
        <div class="header-title">
            <h1><i class="fas fa-building"></i> Gerenciamento de Setores</h1>
            <p class="subtitle">Gerencie todos os setores da sua organização</p>
        </div>
        <div class="header-actions">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Buscar setores...">
                    <button type="button" id="clearSearch" class="search-clear" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="view-toggle">
                <button type="button" id="gridViewBtn" class="toggle-btn active" title="Visualização em Grid">
                    <i class="fas fa-th-large"></i>
                    <span>Grid</span>
                </button>
                <button type="button" id="tableViewBtn" class="toggle-btn" title="Visualização em Tabela">
                    <i class="fas fa-table"></i>
                    <span>Tabela</span>
                </button>
            </div>
        </div>
    </div>


    <!-- Estatísticas Gerais -->
    <div class="setores-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= count($setores) ?></h3>
                <p class="stat-label">Setores</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= array_sum(array_column($setores, 'total_chamados')) ?></h3>
                <p class="stat-label">Total de Chamados</p>
            </div>
        </div>

        <?php
        $setorMaisChamados = null;
        $maxChamados = 0;
        foreach ($setores as $setor) {
            if ($setor['total_chamados'] > $maxChamados) {
                $maxChamados = $setor['total_chamados'];
                $setorMaisChamados = $setor;
            }
        }
        ?>

        <?php if ($setorMaisChamados): ?>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= htmlspecialchars($setorMaisChamados['nome']) ?></h3>
                    <p class="stat-label">Setor Mais Ativo</p>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $tempoMedioTotal = 0;
        $contadorTempos = 0;
        foreach ($setores as $setor) {
            if (!empty($setor['tempo_medio_atendimento'])) {
                $tempoMedioTotal += $setor['tempo_medio_atendimento'];
                $contadorTempos++;
            }
        }
        $tempoMedioGeral = $contadorTempos > 0 ? round($tempoMedioTotal / $contadorTempos) : 0;
        ?>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= formatarTempo($tempoMedioGeral) ?></h3>
                <p class="stat-label">Tempo Médio</p>
            </div>
        </div>
    </div>



    <!-- Visualização em Cards -->
    <div id="cardsView" class="setores-grid">
        <?php foreach ($setores as $setor): ?>
            <div class="setor-card"
                data-nome="<?= htmlspecialchars($setor['nome']) ?>"
                data-status="<?= $setor['ativo'] ? 'ativo' : 'inativo' ?>"
                data-chamados="<?= $setor['total_chamados'] ?>"
                data-tempo="<?= !empty($setor['tempo_medio_atendimento']) ? $setor['tempo_medio_atendimento'] : 0 ?>">

                <div class="setor-card-header">
                    <div class="setor-info">
                        <div class="setor-avatar" data-name="<?= htmlspecialchars($setor['nome']) ?>">
                            <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                        </div>
                        <div class="setor-details">
                            <h3 class="setor-name"><?= htmlspecialchars($setor['nome']) ?></h3>
                            <span class="setor-status <?= $setor['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                <i class="fas <?= $setor['ativo'] ? 'fa-check-circle' : 'fa-pause-circle' ?>"></i>
                                <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>
                    </div>
                    <div class="setor-actions dropdown">
                        <button class="action-btn dropdown-toggle" type="button" id="dropdownMenuButton<?= $setor['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?= $setor['id'] ?>">
                            <li><a class="dropdown-item" href="<?= base_url('setores/detalhes/' . $setor['id']) ?>"><i class="fas fa-eye me-2"></i> Ver Detalhes</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('setores/usuarios/' . $setor['id']) ?>"><i class="fas fa-users me-2"></i> Gerenciar Usuários</a></li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= base_url('setores/editar/' . $setor['id']) ?>"><i class="fas fa-edit me-2"></i> Editar Setor</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="setor-card-body">
                    <div class="setor-description">
                        <?php if (!empty($setor['descricao'])): ?>
                            <p><?= htmlspecialchars($setor['descricao']) ?></p>
                        <?php else: ?>
                            <p class="no-description">Sem descrição disponível</p>
                        <?php endif; ?>
                    </div>

                    <div class="setor-metrics">
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="metric-content">
                                <span class="metric-value"><?= $setor['total_chamados'] ?></span>
                                <span class="metric-label">Chamados</span>
                            </div>
                        </div>

                        <?php if (!empty($setor['tempo_medio_atendimento'])): ?>
                            <div class="metric-item">
                                <div class="metric-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="metric-content">
                                    <span class="metric-value"><?= formatarTempo($setor['tempo_medio_atendimento']) ?></span>
                                    <span class="metric-label">Tempo Médio</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($setor['chamados_por_status'])): ?>
                        <div class="setor-progress">
                            <div class="progress-header">
                                <h4 class="progress-title">Status dos Chamados</h4>
                                <span class="progress-total"><?= $setor['total_chamados'] ?> chamados</span>
                            </div>
                            <div class="progress-bar-container">
                                <?php foreach ($setor['chamados_por_status'] as $status): ?>
                                    <div class="progress-segment bg-<?= getStatusColor($status['status']) ?>"
                                        style="width: <?= $status['percentual'] ?>%;"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="<?= $status['nome'] ?>: <?= $status['total'] ?> chamados (<?= $status['percentual'] ?>%)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="progress-legend">
                                <?php foreach ($setor['chamados_por_status'] as $status): ?>
                                    <div class="legend-item">
                                        <span class="legend-color bg-<?= getStatusColor($status['status']) ?>"></span>
                                        <span class="legend-text"><?= $status['nome'] ?> (<?= $status['total'] ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="setor-card-footer">
                    <a href="<?= base_url('setores/detalhes/' . $setor['id']) ?>" class="btn-primary btn-block">
                        <i class="fas fa-eye me-2"></i> Ver Detalhes
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($setores)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="empty-title">Nenhum setor encontrado</h3>
                <p class="empty-description">Não há setores cadastrados ou que correspondam aos filtros aplicados.</p>
                <?php if (is_admin()): ?>
                    <a href="<?= base_url('setores/criar') ?>" class="btn-primary">
                        <i class="fas fa-plus me-2"></i> Criar Novo Setor
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Visualização em Tabela -->
    <div id="tableView" class="setores-table-container" style="display: none;">
        <div class="table-responsive">
            <table class="setores-table">
                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Descrição</th>
                        <th class="text-center">Chamados</th>
                        <th class="text-center">Tempo Médio</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($setores as $setor): ?>
                        <tr class="setor-row"
                            data-nome="<?= htmlspecialchars($setor['nome']) ?>"
                            data-status="<?= $setor['ativo'] ? 'ativo' : 'inativo' ?>"
                            data-chamados="<?= $setor['total_chamados'] ?>"
                            data-tempo="<?= !empty($setor['tempo_medio_atendimento']) ? $setor['tempo_medio_atendimento'] : 0 ?>">
                            <td data-label="Setor">
                                <div class="setor-info">
                                    <div class="setor-avatar-sm" data-name="<?= htmlspecialchars($setor['nome']) ?>">
                                        <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                    </div>
                                    <span class="setor-name"><?= htmlspecialchars($setor['nome']) ?></span>
                                </div>
                            </td>
                            <td data-label="Descrição">
                                <?php if (!empty($setor['descricao'])): ?>
                                    <div class="description-truncate" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($setor['descricao']) ?>">
                                        <?= htmlspecialchars(substr($setor['descricao'], 0, 50)) ?>
                                        <?= strlen($setor['descricao']) > 50 ? '...' : '' ?>
                                    </div>
                                <?php else: ?>
                                    <span class="no-description">Sem descrição</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-label="Chamados">
                                <div class="chamados-badge"><?= $setor['total_chamados'] ?></div>
                            </td>
                            <td class="text-center" data-label="Tempo Médio">
                                <?php if (!empty($setor['tempo_medio_atendimento'])): ?>
                                    <div class="tempo-badge"><?= formatarTempo($setor['tempo_medio_atendimento']) ?></div>
                                <?php else: ?>
                                    <span class="no-data">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-label="Status">
                                <span class="status-pill <?= $setor['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                    <i class="fas <?= $setor['ativo'] ? 'fa-check-circle' : 'fa-pause-circle' ?>"></i>
                                    <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="text-center" data-label="Ações">
                                <div class="table-actions">
                                    <a href="<?= base_url('setores/detalhes/' . $setor['id']) ?>" class="action-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="action-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Gerenciar Usuários">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <?php if (is_admin()): ?>
                                        <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="action-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Setor">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($setores)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="empty-title">Nenhum setor encontrado</h3>
                <p class="empty-description">Não há setores cadastrados ou que correspondam aos filtros aplicados.</p>
                <?php if (is_admin()): ?>
                    <a href="<?= base_url('setores/criar') ?>" class="btn-primary">
                        <i class="fas fa-plus me-2"></i> Criar Novo Setor
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estado vazio para resultados de busca -->
    <div id="noResults" class="empty-state" style="display: none;">
        <div class="empty-icon">
            <i class="fas fa-search"></i>
        </div>
        <h3 class="empty-title">Nenhum resultado encontrado</h3>
        <p class="empty-description">Sua busca não retornou resultados. Tente outros termos.</p>
        <button id="clearFilters" class="btn-secondary">
            <i class="fas fa-times-circle me-2"></i> Limpar Busca
        </button>
    </div>
</div>

<!-- Incluindo os arquivos CSS e JS separados -->
<link rel="stylesheet" href="<?= base_url('public/css/setores-visualizacao.css') ?>">
<script src="<?= base_url('public/js/setores-visualizacao.js') ?>"></script>

<?php
// Função auxiliar para obter a cor do status (caso não exista no seu código)
if (!function_exists('getStatusColor')) {
    function getStatusColor($status)
    {
        $colors = [
            'aberto' => 'danger',
            'em_andamento' => 'warning',
            'pausado' => 'info',
            'concluido' => 'success',
            'cancelado' => 'secondary'
        ];

        return isset($colors[$status]) ? $colors[$status] : 'primary';
    }
}

// Função auxiliar para formatar tempo (caso não exista no seu código)
if (!function_exists('formatarTempo')) {
    function formatarTempo($minutos)
    {
        if ($minutos < 60) {
            return $minutos . ' min';
        } else {
            $horas = floor($minutos / 60);
            $min = $minutos % 60;
            return $horas . 'h ' . ($min > 0 ? $min . 'min' : '');
        }
    }
}
?>