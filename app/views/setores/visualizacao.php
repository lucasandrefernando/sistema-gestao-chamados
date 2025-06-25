<?php

/**
 * Visualização de Setores
 * Esta página exibe todos os setores da organização em formato de grid ou tabela
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

// Título da página
$pageTitle = 'Gerenciamento de Setores';

// Configuração da paginação
$itemsPerPage = 6; // Número de itens por página
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalItems = count($setores);
$totalPages = ceil($totalItems / $itemsPerPage);

// Limita a página atual entre 1 e o total de páginas
$currentPage = max(1, min($currentPage, $totalPages));

// Calcula o índice inicial e final para a página atual
$startIndex = ($currentPage - 1) * $itemsPerPage;
$endIndex = min($startIndex + $itemsPerPage, $totalItems);

// Obtém os setores para a página atual
$setoresPaginados = array_slice($setores, $startIndex, $itemsPerPage);
?>

<div class="setores-dashboard-v2">
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
        // Calcula a taxa de resolução de chamados
        $totalChamados = array_sum(array_column($setores, 'total_chamados'));
        $chamadosConcluidos = 0;

        foreach ($setores as $setor) {
            if (!empty($setor['chamados_por_status'])) {
                foreach ($setor['chamados_por_status'] as $status) {
                    if (stripos($status['nome'], 'conclu') !== false) {
                        $chamadosConcluidos += $status['total'];
                    }
                }
            }
        }

        $taxaResolucao = $totalChamados > 0 ? round(($chamadosConcluidos / $totalChamados) * 100) : 0;
        ?>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= $taxaResolucao ?>%</h3>
                <p class="stat-label">Taxa de Resolução</p>
            </div>
        </div>

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
        <?php foreach ($setoresPaginados as $setor): ?>
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

                    <?php if (is_admin()): ?>
                        <div class="setor-actions dropdown">
                            <button class="action-btn dropdown-toggle" type="button" id="dropdownMenuButton<?= $setor['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?= $setor['id'] ?>">
                                <li><a class="dropdown-item" href="<?= base_url('setores/usuarios/' . $setor['id']) ?>"><i class="fas fa-users me-2"></i> Gerenciar Usuários</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('setores/editar/' . $setor['id']) ?>"><i class="fas fa-edit me-2"></i> Editar Setor</a></li>
                                <li><a class="dropdown-item text-danger toggle-status" href="javascript:void(0)" data-setor-id="<?= $setor['id'] ?>" data-status="<?= $setor['ativo'] ? '0' : '1' ?>">
                                        <i class="fas <?= $setor['ativo'] ? 'fa-pause-circle' : 'fa-check-circle' ?> me-2"></i>
                                        <?= $setor['ativo'] ? 'Desativar Setor' : 'Ativar Setor' ?>
                                    </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="setor-card-body">
                    <div class="setor-description">
                        <?php if (!empty($setor['descricao'])): ?>
                            <p><?= htmlspecialchars(substr($setor['descricao'], 0, 150)) ?>
                                <?= strlen($setor['descricao']) > 150 ? '...' : '' ?></p>
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
                                <?php
                                // Calcula o total de chamados para este setor
                                $totalChamados = $setor['total_chamados'] > 0 ? $setor['total_chamados'] : array_sum(array_column($setor['chamados_por_status'], 'total'));

                                foreach ($setor['chamados_por_status'] as $status):
                                    // Define a classe de cor com base no status
                                    $statusClass = '';
                                    $statusName = strtolower($status['nome']);

                                    if (strpos($statusName, 'aberto') !== false) {
                                        $statusClass = 'bg-warning';
                                    } elseif (strpos($statusName, 'atendimento') !== false || strpos($statusName, 'andamento') !== false) {
                                        $statusClass = 'bg-primary';
                                    } elseif (strpos($statusName, 'pausado') !== false) {
                                        $statusClass = 'bg-info';
                                    } elseif (strpos($statusName, 'concluido') !== false || strpos($statusName, 'concluído') !== false) {
                                        $statusClass = 'bg-success';
                                    } elseif (strpos($statusName, 'cancelado') !== false) {
                                        $statusClass = 'bg-secondary';
                                    } else {
                                        $statusClass = 'bg-primary'; // Padrão
                                    }

                                    // Calcula o percentual se não estiver definido
                                    $percentual = isset($status['percentual']) ? $status['percentual'] : ($totalChamados > 0 ? round(($status['total'] / $totalChamados) * 100) : 0);
                                ?>
                                    <div class="progress-segment <?= $statusClass ?>"
                                        style="width: <?= $percentual ?>%;"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="<?= htmlspecialchars($status['nome']) ?>: <?= $status['total'] ?> chamados (<?= $percentual ?>%)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="progress-legend">
                                <?php foreach ($setor['chamados_por_status'] as $status): ?>
                                    <div class="legend-item">
                                        <?php
                                        // Define a classe de cor com base no status
                                        $statusClass = '';
                                        $statusName = strtolower($status['nome']);

                                        if (strpos($statusName, 'aberto') !== false) {
                                            $statusClass = 'bg-warning';
                                        } elseif (strpos($statusName, 'atendimento') !== false || strpos($statusName, 'andamento') !== false) {
                                            $statusClass = 'bg-primary';
                                        } elseif (strpos($statusName, 'pausado') !== false) {
                                            $statusClass = 'bg-info';
                                        } elseif (strpos($statusName, 'concluido') !== false || strpos($statusName, 'concluído') !== false) {
                                            $statusClass = 'bg-success';
                                        } elseif (strpos($statusName, 'cancelado') !== false) {
                                            $statusClass = 'bg-secondary';
                                        } else {
                                            $statusClass = 'bg-primary'; // Padrão
                                        }
                                        ?>
                                        <span class="legend-color <?= $statusClass ?>"></span>
                                        <span class="legend-text"><?= htmlspecialchars($status['nome']) ?> (<?= $status['total'] ?>)</span>
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
                    <?php foreach ($setoresPaginados as $setor): ?>
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
                                    <?php if (is_admin()): ?>
                                        <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="action-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Gerenciar Usuários">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="action-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Setor">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="action-icon toggle-status" data-setor-id="<?= $setor['id'] ?>" data-status="<?= $setor['ativo'] ? '0' : '1' ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $setor['ativo'] ? 'Desativar Setor' : 'Ativar Setor' ?>">
                                            <i class="fas <?= $setor['ativo'] ? 'fa-pause-circle' : 'fa-check-circle' ?>"></i>
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
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=1" class="pagination-item" title="Primeira página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $currentPage - 1 ?>" class="pagination-item" title="Página anterior">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-item disabled">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                    <span class="pagination-item disabled">
                        <i class="fas fa-angle-left"></i>
                    </span>
                <?php endif; ?>

                <?php
                // Determina quais páginas mostrar
                $startPage = max(1, $currentPage - 2);
                $endPage = min($startPage + 4, $totalPages);

                if ($endPage - $startPage < 4) {
                    $startPage = max(1, $endPage - 4);
                }

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pagination-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="pagination-item" title="Próxima página">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $totalPages ?>" class="pagination-item" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-item disabled">
                        <i class="fas fa-angle-right"></i>
                    </span>
                    <span class="pagination-item disabled">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Mostrando <?= $startIndex + 1 ?> a <?= $endIndex ?> de <?= $totalItems ?> setores
            </div>
        </div>
    <?php endif; ?>

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

    <!-- Modal de confirmação para alteração de status -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Confirmar Alteração</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p id="statusModalMessage">Tem certeza que deseja alterar o status deste setor?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-primary" id="confirmStatusChange">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Função auxiliar para obter a cor do status (caso não exista no seu código)
if (!function_exists('getStatusColor')) {
    function getStatusColor($status)
    {
        // Cores atualizadas para corresponder ao padrão do sistema
        $colors = [
            'aberto' => 'warning',
            'em_andamento' => 'primary',
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