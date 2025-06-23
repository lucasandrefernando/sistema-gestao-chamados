<div class="chamados-dashboard">
    <!-- Cabeçalho da Página -->
    <div class="chamados-header">
        <div class="chamados-header-content">
            <div class="chamados-title-section">
                <h1 class="chamados-page-title">
                    <i class="fas fa-ticket-alt chamados-title-icon"></i>
                    Chamados
                </h1>
                <p class="chamados-subtitle">Visualize e gerencie todos os chamados do sistema</p>
            </div>
            <div class="chamados-action-buttons">
                <div class="chamados-view-toggle">
                    <a href="<?= base_url('chamados/listar') ?>" class="chamados-toggle-btn">
                        <i class="fas fa-list"></i> Listar Chamados
                    </a>
                    <a href="<?= base_url('chamados/relatorio') ?>" class="chamados-toggle-btn">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </div>
                <a href="<?= base_url('chamados/criar') ?>" class="chamados-new-btn">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="chamados-stats-grid">
        <div class="chamados-stat-card chamados-total">
            <div class="chamados-stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="chamados-stat-content">
                <div class="chamados-stat-value"><?= $estatisticas['total'] ?? 0 ?></div>
                <div class="chamados-stat-label">Total de Chamados</div>
                <div class="chamados-stat-description">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <div class="chamados-stat-card chamados-abertos">
            <div class="chamados-stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="chamados-stat-content">
                <div class="chamados-stat-value"><?= $estatisticas['abertos'] ?? 0 ?></div>
                <div class="chamados-stat-label">Chamados Abertos</div>
                <div class="chamados-stat-description">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <div class="chamados-stat-card chamados-andamento">
            <div class="chamados-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="chamados-stat-content">
                <div class="chamados-stat-value"><?= $estatisticas['em_andamento'] ?? 0 ?></div>
                <div class="chamados-stat-label">Em Atendimento</div>
                <div class="chamados-stat-description">Chamados que estão sendo processados</div>
            </div>
        </div>

        <div class="chamados-stat-card chamados-concluidos">
            <div class="chamados-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="chamados-stat-content">
                <div class="chamados-stat-value"><?= $estatisticas['concluidos'] ?? 0 ?></div>
                <div class="chamados-stat-label">Concluídos</div>
                <div class="chamados-stat-description">Chamados finalizados com sucesso</div>
            </div>
        </div>
    </div>

    <div class="chamados-dashboard-content">
        <!-- Filtro Rápido -->
        <div class="chamados-filter-card">
            <div class="chamados-filter-header">
                <h5 class="chamados-filter-title">Filtro Rápido</h5>
            </div>
            <div class="chamados-filter-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get" class="chamados-filter-form">
                    <div class="chamados-filter-group">
                        <label for="chamados-status" class="chamados-filter-label">Status</label>
                        <select class="chamados-filter-select" id="chamados-status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusList as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>">
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamados-filter-group">
                        <label for="chamados-setor" class="chamados-filter-label">Setor</label>
                        <select class="chamados-filter-select" id="chamados-setor" name="setor">
                            <option value="">Todos</option>
                            <?php foreach ($setores as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>">
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamados-filter-group">
                        <label for="chamados-data-inicio" class="chamados-filter-label">Data Inicial</label>
                        <input type="date" class="chamados-filter-input" id="chamados-data-inicio" name="data_inicio">
                    </div>
                    <div class="chamados-filter-group">
                        <label for="chamados-data-fim" class="chamados-filter-label">Data Final</label>
                        <input type="date" class="chamados-filter-input" id="chamados-data-fim" name="data_fim">
                    </div>
                    <button type="submit" class="chamados-filter-button">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Chamados Recentes -->
        <div class="chamados-recent-card">
            <div class="chamados-recent-header">
                <h5 class="chamados-recent-title">Chamados Recentes</h5>
                <a href="<?= base_url('chamados/listar') ?>" class="chamados-view-all">
                    <i class="fas fa-external-link-alt"></i> Ver Todos
                </a>
            </div>
            <div class="chamados-recent-body">
                <div class="chamados-table-responsive">
                    <table class="chamados-recent-table">
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
                                        <td class="chamados-ticket-id"><?= $chamado['id'] ?></td>
                                        <td>
                                            <div class="chamados-ticket-desc" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                                <?= htmlspecialchars(substr($chamado['descricao'], 0, 30)) . (strlen($chamado['descricao']) > 30 ? '...' : '') ?>
                                            </div>
                                        </td>
                                        <td class="chamados-ticket-user"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                        <td>
                                            <?php
                                            $statusEncontrado = false;
                                            foreach ($statusList as $statusItem) {
                                                if ($statusItem['id'] == $chamado['status_id']) {
                                                    $statusClass = getStatusColor(strtolower(str_replace(' ', '_', $statusItem['nome'])));
                                                    echo '<span class="chamados-status-badge chamados-status-' . $statusClass . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                                    $statusEncontrado = true;
                                                    break;
                                                }
                                            }
                                            if (!$statusEncontrado) {
                                                echo '<span class="chamados-status-badge chamados-status-unknown">Desconhecido</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="chamados-ticket-date"><?= formatarData($chamado['data_solicitacao']) ?></td>
                                        <td>
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-view-btn" data-bs-toggle="tooltip" title="Visualizar chamado">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="chamados-no-tickets">
                                        <div class="chamados-no-data">
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