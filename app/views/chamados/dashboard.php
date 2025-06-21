<div class="dashboard-container">
    <!-- Cabeçalho da Página com Design Aprimorado -->
    <div class="user-dashboard-header">
        <div class="header-content">
            <div class="title-section">
                <h1 class="page-title">
                    <i class="fas fa-ticket-alt title-icon"></i>
                    Chamados
                </h1>
                <p class="subtitle">Visualize e gerencie todos os chamados do sistema</p>
            </div>
            <div class="action-buttons">
                <div class="view-toggle">
                    <a href="<?= base_url('chamados/listar') ?>" class="toggle-btn">
                        <i class="fas fa-list"></i> Listar Chamados
                    </a>
                    <a href="<?= base_url('chamados/relatorio') ?>" class="toggle-btn">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </div>
                <a href="<?= base_url('chamados/criar') ?>" class="btn-new-user">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas com Design Aprimorado -->
    <div class="license-stats">
        <div class="stat-card total-licenses">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $estatisticas['total'] ?? 0 ?></div>
                <div class="stat-label">Total de Chamados</div>
                <div class="stat-description">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <div class="stat-card used-licenses">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $estatisticas['abertos'] ?? 0 ?></div>
                <div class="stat-label">Chamados Abertos</div>
                <div class="stat-description">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <div class="stat-card available-licenses has-licenses">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $estatisticas['em_andamento'] ?? 0 ?></div>
                <div class="stat-label">Em Atendimento</div>
                <div class="stat-description">Chamados que estão sendo processados</div>
            </div>
        </div>

        <div class="stat-card available-licenses has-licenses">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $estatisticas['concluidos'] ?? 0 ?></div>
                <div class="stat-label">Concluídos</div>
                <div class="stat-description">Chamados finalizados com sucesso</div>
            </div>
        </div>
    </div>

    <div class="dashboard-bottom">
        <!-- Filtro Rápido com Design Aprimorado -->
        <div class="filter-card">
            <div class="filter-header">
                <h5 class="filter-title">Filtro Rápido</h5>
            </div>
            <div class="filter-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get" class="quick-filter-form">
                    <div class="filter-group">
                        <label for="status" class="filter-label">Status</label>
                        <select class="filter-select" id="status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusList as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>">
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="setor" class="filter-label">Setor</label>
                        <select class="filter-select" id="setor" name="setor">
                            <option value="">Todos</option>
                            <?php foreach ($setores as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>">
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="data_inicio" class="filter-label">Data Inicial</label>
                        <input type="date" class="filter-input" id="data_inicio" name="data_inicio">
                    </div>
                    <div class="filter-group">
                        <label for="data_fim" class="filter-label">Data Final</label>
                        <input type="date" class="filter-input" id="data_fim" name="data_fim">
                    </div>
                    <button type="submit" class="filter-button">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Chamados Recentes com Design Aprimorado -->
        <div class="recent-card">
            <div class="recent-header">
                <h5 class="recent-title">Chamados Recentes</h5>
                <a href="<?= base_url('chamados/listar') ?>" class="view-all-link">
                    <i class="fas fa-external-link-alt"></i> Ver Todos
                </a>
            </div>
            <div class="recent-body">
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Solicitante</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($chamadosRecentes)): ?>
                                <?php foreach ($chamadosRecentes as $chamado): ?>
                                    <tr>
                                        <td class="ticket-id"><?= $chamado['id'] ?></td>
                                        <td>
                                            <div class="ticket-desc" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                                <?= htmlspecialchars(substr($chamado['descricao'], 0, 30)) . (strlen($chamado['descricao']) > 30 ? '...' : '') ?>
                                            </div>
                                        </td>
                                        <td class="ticket-user"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                        <td>
                                            <?php
                                            $statusEncontrado = false;
                                            foreach ($statusList as $statusItem) {
                                                if ($statusItem['id'] == $chamado['status_id']) {
                                                    $statusClass = getStatusColor(strtolower(str_replace(' ', '_', $statusItem['nome'])));
                                                    echo '<span class="status-badge ' . $statusClass . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                                    $statusEncontrado = true;
                                                    break;
                                                }
                                            }
                                            if (!$statusEncontrado) {
                                                echo '<span class="status-badge unknown">Desconhecido</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="ticket-date"><?= formatarData($chamado['data_solicitacao']) ?></td>
                                        <td>
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="view-ticket-btn">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-tickets">
                                        <div class="no-data-message">
                                            <i class="fas fa-ticket-alt"></i>
                                            <p>Nenhum chamado encontrado.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Adicional para a Página de Chamados -->
<style>
    /* Estilos específicos para a Página de Chamados */
    .dashboard-container {
        padding: 1rem 0;
    }

    /* Estilos para a seção inferior */
    .dashboard-bottom {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Estilos para o filtro rápido */
    .filter-card {
        background-color: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        transition: var(--transition);
    }

    .filter-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .filter-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--light-gray);
    }

    .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--dark-color);
    }

    .filter-body {
        padding: 1.5rem;
    }

    .filter-group {
        margin-bottom: 1.25rem;
    }

    .filter-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark-color);
    }

    .filter-select,
    .filter-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--light-gray);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .filter-select {
        padding-right: 2rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        appearance: none;
    }

    .filter-select:focus,
    .filter-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .filter-button {
        width: 100%;
        padding: 0.75rem 1rem;
        background-color: var(--primary-color);
        color: var(--white-color);
        border: none;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .filter-button:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    /* Estilos para os chamados recentes */
    .recent-card {
        background-color: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        transition: var(--transition);
    }

    .recent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .recent-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--light-gray);
    }

    .recent-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--dark-color);
    }

    .view-all-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .view-all-link:hover {
        color: var(--primary-dark);
        transform: translateX(3px);
    }

    .recent-body {
        overflow: hidden;
    }

    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-table th {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark-color);
        border-bottom: 1px solid var(--light-gray);
    }

    .recent-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--light-gray);
        vertical-align: middle;
    }

    .recent-table tr:last-child td {
        border-bottom: none;
    }

    .recent-table tr:hover {
        background-color: #f8f9fa;
    }

    .ticket-id {
        font-weight: 600;
        color: var(--primary-color);
    }

    .ticket-desc {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ticket-user {
        font-weight: 500;
    }

    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.primary {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }

    .status-badge.success {
        background-color: var(--success-light);
        color: var(--success-color);
    }

    .status-badge.warning {
        background-color: var(--warning-light);
        color: var(--warning-color);
    }

    .status-badge.danger {
        background-color: var(--danger-light);
        color: var(--danger-color);
    }

    .status-badge.info {
        background-color: var(--info-light);
        color: var(--info-color);
    }

    .status-badge.unknown {
        background-color: var(--light-gray);
        color: var(--gray-color);
    }

    .ticket-date {
        color: var(--gray-color);
        font-size: 0.85rem;
    }

    .view-ticket-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--primary-light);
        color: var(--primary-color);
        text-decoration: none;
        transition: var(--transition);
    }

    .view-ticket-btn:hover {
        background-color: var(--primary-color);
        color: var(--white-color);
        transform: scale(1.1);
    }

    .no-tickets {
        padding: 2rem 0;
    }

    .no-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--gray-color);
    }

    .no-data-message i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .no-data-message p {
        font-size: 1rem;
        margin: 0;
    }

    /* Responsividade */
    @media (max-width: 1199.98px) {
        .dashboard-bottom {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            margin-top: 1rem;
        }

        .view-toggle {
            flex: 1;
        }

        .btn-new-user {
            white-space: nowrap;
        }

        .ticket-desc {
            max-width: 150px;
        }
    }

    @media (max-width: 575.98px) {
        .action-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }

        .view-toggle,
        .btn-new-user {
            width: 100%;
        }

        .toggle-btn {
            justify-content: center;
        }

        .recent-table th:nth-child(2),
        .recent-table td:nth-child(2) {
            display: none;
        }

        .ticket-desc {
            max-width: 100px;
        }
    }
</style>

<!-- Script para inicializar tooltips -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Animação para os cards de estatísticas
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(function(card, index) {
            setTimeout(function() {
                card.classList.add('animate-in');
            }, index * 100);
        });
    });
</script>