<?php

/**
 * Administração de Setores
 * Esta página permite gerenciar todos os setores da organização
 * 
 * @version 4.1
 * @author Desenvolvedor
 */
?>

<div class="setores-admin-v4">
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
            <!-- Botão voltar corrigido para usar o histórico do navegador -->
            <a href="javascript:history.back()" class="btn-outline btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </header>

    <!-- Painel de estatísticas -->
    <section class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card">
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
            <div class="stat-card">
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
            <div class="stat-card">
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

            <div class="stat-card highlight-card" id="replicateUsersCard">
                <div class="highlight-content">
                    <div class="highlight-icon">
                        <i class="fas fa-copy"></i>
                    </div>
                    <div class="highlight-text">
                        <h3>Replicar Usuários</h3>
                        <p>Copie usuários entre setores rapidamente</p>
                    </div>
                </div>
                <button class="highlight-button">
                    Iniciar Agora
                </button>
            </div>
        </div>
    </section>

    <!-- Filtros e opções - versão simplificada e compacta -->
    <section class="filters-section">
        <div class="filters-container">
            <!-- Linha de filtros -->
            <div class="filters-row">
                <!-- Campo de busca -->
                <div class="filter-group">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar setores...">
                        <button type="button" id="clearSearch" class="search-clear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtro de status -->
                <div class="filter-group">
                    <select id="statusFilter" class="filter-select">
                        <option value="">Status: Todos</option>
                        <option value="ativo">Status: Ativos</option>
                        <option value="inativo">Status: Inativos</option>
                        <?php if ($mostrarRemovidos): ?>
                            <option value="removido">Status: Removidos</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Ordenação -->
                <div class="filter-group">
                    <select id="sortOrder" class="filter-select">
                        <option value="id_asc">Ordenar por: ID (↑)</option>
                        <option value="id_desc">Ordenar por: ID (↓)</option>
                        <option value="nome_asc">Ordenar por: Nome (A-Z)</option>
                        <option value="nome_desc">Ordenar por: Nome (Z-A)</option>
                        <option value="usuarios_desc">Ordenar por: Mais Usuários</option>
                    </select>
                </div>

                <!-- Botão limpar filtros -->
                <div class="filter-group">
                    <button id="clearFilters" class="btn-outline btn-filter">
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
                <button id="batchActivate" class="btn-outline" disabled>
                    <i class="fas fa-check-circle"></i>
                    <span>Ativar</span>
                </button>
                <button id="batchDeactivate" class="btn-outline" disabled>
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
                            <tr class="<?= isset($setor['removido']) && $setor['removido'] ? 'row-removed' : '' ?>"
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
                                    <div class="action-menu">
                                        <button class="action-menu-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-menu-dropdown">
                                            <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                                <a href="<?= base_url('setores/restaurar/' . $setor['id']) ?>" class="dropdown-item">
                                                    <i class="fas fa-trash-restore"></i> Restaurar
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="dropdown-item">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="dropdown-item">
                                                    <i class="fas fa-users"></i> Gerenciar Usuários
                                                </a>
                                                <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="dropdown-item">
                                                    <i class="fas <?= $setor['ativo'] ? 'fa-times' : 'fa-check' ?>"></i>
                                                    <?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>
                                                </a>
                                                <?php if ($setor['total_usuarios'] == 0): ?>
                                                    <a href="javascript:void(0)" class="dropdown-item text-danger action-remove" data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars($setor['nome']) ?>">
                                                        <i class="fas fa-trash"></i> Remover
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
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
                                            <i class="fas fa-plus me-2"></i> Criar Novo Setor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estado vazio para resultados de busca -->
            <div id="noResults" class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="empty-title">Nenhum resultado encontrado</h3>
                <p class="empty-description">Sua busca não retornou resultados. Tente outros termos ou remova os filtros.</p>
                <button id="clearFiltersBtn" class="btn-outline">
                    <i class="fas fa-times-circle me-2"></i> Limpar Filtros
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
                            <i class="fas fa-trash me-2"></i> Remover
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
                            <i class="fas fa-check me-2"></i> Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para replicação de usuários - Versão Melhorada -->
        <div class="modal-overlay" id="replicateModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-copy"></i>
                            Replicar Usuários Entre Setores
                        </h3>
                        <button type="button" class="modal-close" id="closeReplicateModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="replicateForm" action="<?= base_url('setores/batch/replicate') ?>" method="post">
                            <div class="replication-container">
                                <!-- Instruções -->
                                <div class="replication-instructions">
                                    <p><i class="fas fa-info-circle"></i> Selecione um setor de origem, verifique os usuários e escolha os setores de destino para replicar.</p>
                                </div>

                                <div class="replication-grid">
                                    <!-- Coluna de origem -->
                                    <div class="replication-column source-column">
                                        <div class="column-header">
                                            <h4>Setor de Origem</h4>
                                            <div class="search-wrapper">
                                                <input type="text" id="sourceSearch" class="search-input" placeholder="Buscar setor...">
                                                <i class="fas fa-search search-icon"></i>
                                            </div>
                                        </div>
                                        <div class="column-content">
                                            <div class="source-setores-list">
                                                <?php foreach ($setores as $setor): ?>
                                                    <?php if (!isset($setor['removido']) || !$setor['removido']): ?>
                                                        <div class="source-setor-card" data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars(strtolower($setor['nome'])) ?>">
                                                            <div class="setor-card-header">
                                                                <div class="setor-avatar" data-name="<?= htmlspecialchars($setor['nome']) ?>">
                                                                    <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                                                </div>
                                                                <div class="setor-card-title">
                                                                    <h4><?= htmlspecialchars($setor['nome']) ?></h4>
                                                                    <span class="user-count">
                                                                        <i class="fas fa-users"></i> <?= $setor['total_usuarios'] ?> usuários
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="setor-card-footer">
                                                                <button type="button" class="btn-select-source">Selecionar</button>
                                                            </div>
                                                            <input type="radio" name="source_id" value="<?= $setor['id'] ?>" class="source-radio" required>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <div class="empty-state small" id="noSourceResults" style="display: none;">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-search"></i>
                                                    </div>
                                                    <h3 class="empty-title">Nenhum resultado</h3>
                                                    <p class="empty-description">Nenhum setor encontrado com este termo.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Coluna de usuários -->
                                    <div class="replication-column users-column">
                                        <div class="column-header">
                                            <h4>Usuários a Replicar</h4>
                                            <div class="user-count-badge" id="selectedUsersCount">
                                                <i class="fas fa-user"></i> <span>0</span> usuários
                                            </div>
                                        </div>
                                        <div class="column-content" id="sourceUsersContainer">
                                            <div class="placeholder-message">
                                                <i class="fas fa-arrow-left"></i>
                                                <p>Selecione um setor de origem para ver os usuários</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Coluna de destino -->
                                    <div class="replication-column target-column">
                                        <div class="column-header">
                                            <h4>Setores de Destino</h4>
                                            <div class="search-wrapper">
                                                <input type="text" id="targetSearch" class="search-input" placeholder="Buscar setor...">
                                                <i class="fas fa-search search-icon"></i>
                                            </div>
                                        </div>
                                        <div class="column-content">
                                            <div class="target-setores-list">
                                                <?php foreach ($setores as $setor): ?>
                                                    <?php if (!isset($setor['removido']) || !$setor['removido']): ?>
                                                        <div class="target-setor-card" data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars(strtolower($setor['nome'])) ?>">
                                                            <div class="setor-card-header">
                                                                <div class="setor-avatar" data-name="<?= htmlspecialchars($setor['nome']) ?>">
                                                                    <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                                                </div>
                                                                <div class="setor-card-title">
                                                                    <h4><?= htmlspecialchars($setor['nome']) ?></h4>
                                                                    <span class="user-count">
                                                                        <i class="fas fa-users"></i> <?= $setor['total_usuarios'] ?> usuários
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="setor-card-footer">
                                                                <label class="checkbox-container">
                                                                    <input type="checkbox" name="target_ids[]" value="<?= $setor['id'] ?>" class="target-checkbox">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checkbox-label">Selecionar</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <div class="empty-state small" id="noTargetResults" style="display: none;">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-search"></i>
                                                    </div>
                                                    <h3 class="empty-title">Nenhum resultado</h3>
                                                    <p class="empty-description">Nenhum setor encontrado com este termo.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="replication-options">
                                    <h4>Opções de Replicação</h4>
                                    <div class="options-list">
                                        <div class="option-item">
                                            <label class="switch">
                                                <input type="checkbox" name="keep_principal" value="1" checked>
                                                <span class="slider round"></span>
                                            </label>
                                            <div class="option-details">
                                                <h4>Manter status de "Principal"</h4>
                                                <p>Preserva o status de usuário principal ao replicar para os setores de destino.</p>
                                            </div>
                                        </div>

                                        <div class="option-item">
                                            <label class="switch">
                                                <input type="checkbox" name="skip_existing" value="1" checked>
                                                <span class="slider round"></span>
                                            </label>
                                            <div class="option-details">
                                                <h4>Ignorar usuários existentes</h4>
                                                <p>Não duplica usuários que já estão associados aos setores de destino.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="replication-summary">
                            <span id="targetSelectedCount">0 setores de destino selecionados</span>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-outline" id="cancelReplicate">Cancelar</button>
                            <button type="submit" form="replicateForm" class="btn-primary" id="confirmReplicate" disabled>
                                <i class="fas fa-copy me-2"></i> Replicar Usuários
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>