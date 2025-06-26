<div class="setores-admin-v5">
    <!-- Cabeçalho da página -->
    <header class="page-header">
        <div class="header-content">
            <div class="title-section">
                <h1 class="page-title">
                    <i class="fas fa-building"></i>
                    Gerenciar Setores
                </h1>
                <p class="page-subtitle">
                    Administre todos os setores da sua organização
                </p>
            </div>
        </div>
        <div class="header-actions">
            <a href="javascript:history.back()" class="btn-outline btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </header>

    <!-- Painel de estatísticas -->
    <section class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card hover-lift">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= count($setores) ?></h3>
                    <p class="stat-label">Total de Setores</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>

            <?php
            $setoresAtivos = array_filter($setores, function ($setor) {
                return $setor['ativo'] && (!isset($setor['removido']) || !$setor['removido']);
            });
            ?>
            <div class="stat-card hover-lift">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= count($setoresAtivos) ?></h3>
                    <p class="stat-label">Setores Ativos</p>
                </div>
                <div class="stat-trend">
                    <span class="trend-percentage"><?= round((count($setoresAtivos) / max(count($setores), 1)) * 100) ?>%</span>
                </div>
            </div>

            <?php
            $totalUsuarios = array_sum(array_column($setores, 'total_usuarios'));
            ?>
            <div class="stat-card hover-lift">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $totalUsuarios ?></h3>
                    <p class="stat-label">Total de Usuários</p>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtros e opções -->
    <section class="filters-section">
        <div class="filters-container">
            <!-- Linha de filtros -->
            <div class="filters-row">
                <!-- Campo de busca -->
                <div class="filter-group">
                    <label for="searchInput" class="filter-label">Buscar setores</label>
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Digite para buscar...">
                        <button type="button" id="clearSearch" class="search-clear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtro de status -->
                <div class="filter-group">
                    <label for="statusFilter" class="filter-label">Status</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativos</option>
                        <option value="inativo">Inativos</option>
                        <?php if ($mostrarRemovidos): ?>
                            <option value="removido">Removidos</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Ordenação -->
                <div class="filter-group">
                    <label for="sortOrder" class="filter-label">Ordenar por</label>
                    <select id="sortOrder" class="filter-select">
                        <option value="id_asc">ID (Crescente)</option>
                        <option value="id_desc">ID (Decrescente)</option>
                        <option value="nome_asc">Nome (A-Z)</option>
                        <option value="nome_desc">Nome (Z-A)</option>
                        <option value="usuarios_desc">Mais Usuários</option>
                    </select>
                </div>

                <!-- Botão limpar filtros -->
                <div class="filter-group">
                    <label class="filter-label">&nbsp;</label>
                    <button id="clearFilters" class="btn-outline">
                        <i class="fas fa-times-circle"></i>
                        <span>Limpar Filtros</span>
                    </button>
                </div>
            </div>

            <!-- Linha de ações -->
            <div class="actions-row">
                <button id="batchActionsBtn" class="btn-secondary">
                    <i class="fas fa-tasks"></i>
                    <span>Ações em Lote</span>
                </button>

                <a href="<?= base_url('setores/admin?mostrar_removidos=' . ($mostrarRemovidos ? '0' : '1')) ?>" class="btn-outline">
                    <i class="fas <?= $mostrarRemovidos ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                    <span><?= $mostrarRemovidos ? 'Ocultar Removidos' : 'Mostrar Removidos' ?></span>
                </a>

                <a href="<?= base_url('setores/criar') ?>" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Novo Setor</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Painel de ações em lote (inicialmente oculto) -->
    <section class="batch-actions-panel" id="batchActionsPanel" style="display: none;">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fas fa-tasks"></i>
                <span>Ações em Lote</span>
            </div>
            <div class="panel-selection">
                <span id="selectedCount" class="selection-count">0 setores selecionados</span>
            </div>
            <button class="panel-close" id="closeBatchPanel">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-body">
            <div class="action-buttons">
                <button id="batchActivate" class="btn-primary" disabled>
                    <i class="fas fa-check-circle"></i>
                    <span>Ativar</span>
                </button>
                <button id="batchDeactivate" class="btn-secondary" disabled>
                    <i class="fas fa-times-circle"></i>
                    <span>Desativar</span>
                </button>
                <button id="batchRemove" class="btn-danger" disabled>
                    <i class="fas fa-trash"></i>
                    <span>Remover</span>
                </button>
            </div>
            <div class="selection-actions">
                <button id="selectAll" class="btn-text">Selecionar Todos</button>
                <button id="deselectAll" class="btn-text" style="display: none;">Desmarcar Todos</button>
            </div>
        </div>
    </section>

    <!-- Tabela de setores -->
    <section class="data-section">
        <div class="data-container">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-checkbox">
                                <input type="checkbox" id="selectAllCheckbox" class="checkbox-input">
                                <label for="selectAllCheckbox" class="checkbox-label"></label>
                            </th>
                            <th class="col-id sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                            <th class="col-nome sortable" data-sort="nome">Nome <i class="fas fa-sort"></i></th>
                            <th class="col-descricao">Descrição</th>
                            <th class="col-usuarios sortable" data-sort="usuarios">Usuários <i class="fas fa-sort"></i></th>
                            <th class="col-status sortable" data-sort="status">Status <i class="fas fa-sort"></i></th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($setores as $setor): ?>
                            <tr class="<?= isset($setor['removido']) && $setor['removido'] ? 'row-removed' : '' ?> fade-in"
                                data-id="<?= $setor['id'] ?>"
                                data-nome="<?= htmlspecialchars($setor['nome']) ?>"
                                data-status="<?= isset($setor['removido']) && $setor['removido'] ? 'removido' : ($setor['ativo'] ? 'ativo' : 'inativo') ?>"
                                data-usuarios="<?= $setor['total_usuarios'] ?>">
                                <td class="col-checkbox">
                                    <input type="checkbox" id="checkbox-<?= $setor['id'] ?>" class="checkbox-input row-checkbox" <?= isset($setor['removido']) && $setor['removido'] ? 'disabled' : '' ?>>
                                    <label for="checkbox-<?= $setor['id'] ?>" class="checkbox-label"></label>
                                </td>
                                <td class="col-id"><?= $setor['id'] ?></td>
                                <td class="col-nome">
                                    <div class="setor-info">
                                        <div class="setor-avatar" data-name="<?= htmlspecialchars($setor['nome']) ?>">
                                            <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                        </div>
                                        <span class="setor-name"><?= htmlspecialchars($setor['nome']) ?></span>
                                    </div>
                                </td>
                                <td class="col-descricao">
                                    <?php if (!empty($setor['descricao'])): ?>
                                        <div class="description-truncate" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($setor['descricao']) ?>">
                                            <?= htmlspecialchars(substr($setor['descricao'], 0, 50)) ?>
                                            <?= strlen($setor['descricao']) > 50 ? '...' : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-description">Sem descrição</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-usuarios">
                                    <div class="user-count">
                                        <i class="fas fa-users"></i>
                                        <span><?= $setor['total_usuarios'] ?></span>
                                    </div>
                                </td>
                                <td class="col-status">
                                    <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                        <span class="status-badge status-removed">
                                            <i class="fas fa-trash-alt"></i> Removido
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge <?= $setor['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                            <i class="fas <?= $setor['ativo'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                            <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-acoes">
                                    <div class="action-buttons-inline">
                                        <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                            <a href="<?= base_url('setores/restaurar/' . $setor['id']) ?>" class="btn-action restore" data-tooltip="Restaurar">
                                                <i class="fas fa-trash-restore"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="btn-action edit" data-tooltip="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="btn-action edit" data-tooltip="Gerenciar Usuários">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="btn-action toggle-active" data-tooltip="<?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $setor['ativo'] ? 'fa-times' : 'fa-check' ?>"></i>
                                            </a>
                                            <?php if ($setor['total_usuarios'] == 0): ?>
                                                <a href="javascript:void(0)" class="btn-action remove" data-tooltip="Remover" data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars($setor['nome']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($setores)): ?>
                            <tr class="empty-row">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <h3 class="empty-title">Nenhum setor encontrado</h3>
                                        <p class="empty-description">Não há setores cadastrados ou que correspondam aos filtros aplicados.</p>
                                        <a href="<?= base_url('setores/criar') ?>" class="btn-primary">
                                            <i class="fas fa-plus"></i> Criar Novo Setor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="pagination-container">
                <!-- Será preenchido via JavaScript -->
            </div>

            <!-- Estado vazio para resultados de busca -->
            <div id="noResults" class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="empty-title">Nenhum resultado encontrado</h3>
                <p class="empty-description">Sua busca não retornou resultados. Tente outros termos ou remova os filtros.</p>
                <button id="clearFiltersBtn" class="btn-outline">
                    <i class="fas fa-times-circle"></i> Limpar Filtros
                </button>
            </div>
        </div>
    </section>

    <!-- Modais -->
    <div class="modals-container">
        <!-- Modal de confirmação para remoção -->
        <div class="modal-overlay" id="removeModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Confirmar Remoção</h3>
                        <button type="button" class="modal-close" id="closeRemoveModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p id="removeModalMessage">Tem certeza que deseja remover este setor?</p>
                        <p class="modal-warning">Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline" id="cancelRemove">Cancelar</button>
                        <a href="#" id="confirmRemove" class="btn-danger">
                            <i class="fas fa-trash"></i> Remover
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmação para ações em lote -->
        <div class="modal-overlay" id="batchModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="batchModalTitle">Confirmar Ação</h3>
                        <button type="button" class="modal-close" id="closeBatchModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-icon" id="batchModalIcon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <p id="batchModalMessage">Tem certeza que deseja realizar esta ação?</p>
                        <div id="batchModalList" class="modal-list">
                            <!-- Lista de setores selecionados será inserida aqui -->
                        </div>
                        <p id="batchModalWarning" class="modal-warning" style="display: none;">Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline" id="cancelBatchAction">Cancelar</button>
                        <button type="button" id="confirmBatchAction" class="btn-primary">
                            <i class="fas fa-check"></i> Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>