<?php

/**
 * Administração de Setores
 * Esta página permite gerenciar todos os setores da organização
 * 
 * @version 2.0
 * @author Desenvolvedor
 */
?>

<div class="setores-admin-index-v2">
    <div class="admin-header">
        <div class="header-content">
            <h1 class="admin-title">
                <i class="fas fa-building"></i>
                Gerenciar Setores
            </h1>
            <p class="admin-subtitle">
                Administre todos os setores da sua organização
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('setores/admin?mostrar_removidos=' . ($mostrarRemovidos ? '0' : '1')) ?>" class="btn-outline">
                <i class="fas <?= $mostrarRemovidos ? 'fa-eye-slash' : 'fa-eye' ?> me-2"></i>
                <?= $mostrarRemovidos ? 'Ocultar Removidos' : 'Mostrar Removidos' ?>
            </a>
            <a href="<?= base_url('setores/criar') ?>" class="btn-primary">
                <i class="fas fa-plus me-2"></i> Novo Setor
            </a>
        </div>
    </div>

    <!-- Dashboard de Estatísticas -->
    <div class="stats-dashboard">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= count($setores) ?></h3>
                <p class="stat-label">Total de Setores</p>
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
        </div>

        <?php
        $totalChamados = array_sum(array_column($setores, 'total_chamados'));
        $totalUsuarios = array_sum(array_column($setores, 'total_usuarios'));
        ?>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= $totalChamados ?></h3>
                <p class="stat-label">Total de Chamados</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= $totalUsuarios ?></h3>
                <p class="stat-label">Total de Usuários</p>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <div class="card-filters">
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar setores...">
                        <button type="button" id="clearSearch" class="search-clear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="filter-options">
                    <select id="statusFilter" class="filter-select">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativos</option>
                        <option value="inativo">Inativos</option>
                        <?php if ($mostrarRemovidos): ?>
                            <option value="removido">Removidos</option>
                        <?php endif; ?>
                    </select>
                    <select id="sortOrder" class="filter-select">
                        <option value="id_asc">ID (Crescente)</option>
                        <option value="id_desc">ID (Decrescente)</option>
                        <option value="nome_asc">Nome (A-Z)</option>
                        <option value="nome_desc">Nome (Z-A)</option>
                        <option value="chamados_desc">Mais Chamados</option>
                        <option value="usuarios_desc">Mais Usuários</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="col-id sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                            <th class="col-nome sortable" data-sort="nome">Nome <i class="fas fa-sort"></i></th>
                            <th class="col-descricao">Descrição</th>
                            <th class="col-chamados sortable" data-sort="chamados">Chamados <i class="fas fa-sort"></i></th>
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
                                data-chamados="<?= $setor['total_chamados'] ?>"
                                data-usuarios="<?= $setor['total_usuarios'] ?>">
                                <td class="col-id"><?= $setor['id'] ?></td>
                                <td class="col-nome">
                                    <div class="setor-info">
                                        <div class="setor-avatar-sm" data-name="<?= htmlspecialchars($setor['nome']) ?>">
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
                                <td class="col-chamados">
                                    <div class="badge badge-primary"><?= $setor['total_chamados'] ?></div>
                                </td>
                                <td class="col-usuarios">
                                    <div class="badge badge-info"><?= $setor['total_usuarios'] ?></div>
                                </td>
                                <td class="col-status">
                                    <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                        <div class="status-badge status-removed">
                                            <i class="fas fa-trash-alt"></i> Removido
                                        </div>
                                    <?php else: ?>
                                        <div class="status-badge <?= $setor['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                            <i class="fas <?= $setor['ativo'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                            <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="col-acoes">
                                    <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                        <a href="<?= base_url('setores/restaurar/' . $setor['id']) ?>" class="action-btn action-restore" data-bs-toggle="tooltip" title="Restaurar">
                                            <i class="fas fa-trash-restore"></i>
                                        </a>
                                    <?php else: ?>
                                        <div class="action-buttons">
                                            <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="action-btn action-edit" data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('setores/usuarios/' . $setor['id']) ?>" class="action-btn action-users" data-bs-toggle="tooltip" title="Gerenciar Usuários">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="action-btn <?= $setor['ativo'] ? 'action-deactivate' : 'action-activate' ?>" data-bs-toggle="tooltip" title="<?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $setor['ativo'] ? 'fa-times' : 'fa-check' ?>"></i>
                                            </a>
                                            <?php if ($setor['total_chamados'] == 0 && $setor['total_usuarios'] == 0): ?>
                                                <a href="javascript:void(0)" class="action-btn action-remove" data-bs-toggle="tooltip" title="Remover"
                                                    data-id="<?= $setor['id'] ?>" data-nome="<?= htmlspecialchars($setor['nome']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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
                <button id="clearFilters" class="btn-secondary">
                    <i class="fas fa-times-circle me-2"></i> Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Ações em Lote -->
    <div class="batch-actions-container">
        <div class="batch-actions-card">
            <div class="batch-header">
                <h3 class="batch-title">Ações em Lote</h3>
                <p class="batch-description">Selecione setores na tabela para realizar ações em lote</p>
            </div>
            <div class="batch-content">
                <div class="batch-selection">
                    <span id="selectedCount" class="selection-count">0 setores selecionados</span>
                    <button id="selectAll" class="btn-text">Selecionar Todos</button>
                    <button id="deselectAll" class="btn-text" style="display: none;">Desmarcar Todos</button>
                </div>
                <div class="batch-buttons">
                    <button id="batchActivate" class="btn-outline btn-sm" disabled>
                        <i class="fas fa-check-circle me-2"></i> Ativar Selecionados
                    </button>
                    <button id="batchDeactivate" class="btn-outline btn-sm" disabled>
                        <i class="fas fa-times-circle me-2"></i> Desativar Selecionados
                    </button>
                    <button id="batchRemove" class="btn-danger btn-sm" disabled>
                        <i class="fas fa-trash me-2"></i> Remover Selecionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação para remoção -->
    <div class="modal-overlay" id="removeModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Confirmar Remoção</h3>
                <button type="button" class="modal-close" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="removeModalMessage">Tem certeza que deseja remover este setor?</p>
                <p class="modal-warning"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelRemove">Cancelar</button>
                <a href="#" id="confirmRemove" class="btn-danger">
                    <i class="fas fa-trash me-2"></i> Remover
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação para ações em lote -->
    <div class="modal-overlay" id="batchModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="batchModalTitle">Confirmar Ação</h3>
                <button type="button" class="modal-close" id="closeBatchModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="batchModalMessage">Tem certeza que deseja realizar esta ação?</p>
                <div id="batchModalList" class="modal-list">
                    <!-- Lista de setores selecionados será inserida aqui -->
                </div>
                <p id="batchModalWarning" class="modal-warning" style="display: none;"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelBatchAction">Cancelar</button>
                <button type="button" id="confirmBatchAction" class="btn-primary">
                    <i class="fas fa-check me-2"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>