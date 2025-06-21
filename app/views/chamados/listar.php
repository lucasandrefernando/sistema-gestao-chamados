<div class="dashboard-container">
    <!-- Cabeçalho da Página com Design Aprimorado e Título Criativo -->
    <div class="user-dashboard-header">
        <div class="header-content">
            <div class="title-section">
                <h1 class="page-title">
                    <i class="fas fa-headset title-icon"></i>
                    Central de Atendimento
                </h1>
                <p class="subtitle">Gerencie e acompanhe todas as solicitações de suporte</p>
            </div>
            <div class="action-buttons">
                <a href="<?= base_url('chamados/criar') ?>" class="btn-new-user">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas com Cores Ajustadas -->
    <div class="license-stats">
        <div class="stat-card total-licenses">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= isset($estatisticas['total']) ? $estatisticas['total'] : count($chamados) ?></div>
                <div class="stat-label">Total de Chamados</div>
                <div class="stat-description">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <div class="stat-card warning-licenses">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php
                    $abertos = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 1) { // Assumindo que status_id 1 é "Aberto"
                            $abertos++;
                        }
                    }
                    echo $abertos;
                    ?>
                </div>
                <div class="stat-label">Chamados Abertos</div>
                <div class="stat-description">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <div class="stat-card info-licenses">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php
                    $emAndamento = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 2) { // Assumindo que status_id 2 é "Em Andamento"
                            $emAndamento++;
                        }
                    }
                    echo $emAndamento;
                    ?>
                </div>
                <div class="stat-label">Em Atendimento</div>
                <div class="stat-description">Chamados que estão sendo processados</div>
            </div>
        </div>

        <div class="stat-card success-licenses">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php
                    $concluidos = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 4) { // Assumindo que status_id 4 é "Concluído"
                            $concluidos++;
                        }
                    }
                    echo $concluidos;
                    ?>
                </div>
                <div class="stat-label">Concluídos</div>
                <div class="stat-description">Chamados finalizados com sucesso</div>
            </div>
        </div>
    </div>

    <!-- Formulário de Filtros Avançados (Sempre Recolhido) -->
    <div class="filter-advanced-card">
        <div class="filter-advanced-header">
            <h5 class="filter-advanced-title">
                <i class="fas fa-filter me-2"></i>Filtros Avançados
                <?php if (isset($filtros) && is_array($filtros) && array_filter($filtros)): ?>
                    <span class="filter-badge"><?= count(array_filter($filtros)) ?></span>
                <?php endif; ?>
            </h5>
            <button class="filter-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse" id="filtrosCollapse">
            <div class="filter-advanced-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-column">
                            <label for="status" class="filter-label">Status</label>
                            <select class="filter-select" id="status" name="status">
                                <option value="">Todos</option>
                                <?php foreach ($statusList as $statusItem): ?>
                                    <option value="<?= $statusItem['id'] ?>" <?= (isset($filtros['status']) && $filtros['status'] == $statusItem['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($statusItem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-column">
                            <label for="setor" class="filter-label">Setor</label>
                            <select class="filter-select" id="setor" name="setor">
                                <option value="">Todos</option>
                                <?php foreach ($setores as $setorItem): ?>
                                    <option value="<?= $setorItem['id'] ?>" <?= (isset($filtros['setor']) && $filtros['setor'] == $setorItem['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setorItem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-column">
                            <label for="tipo_servico" class="filter-label">Tipo de Serviço</label>
                            <select class="filter-select" id="tipo_servico" name="tipo_servico">
                                <option value="">Todos</option>
                                <?php foreach ($tiposServico as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= (isset($filtros['tipo_servico']) && $filtros['tipo_servico'] == $tipo) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-column">
                            <label for="solicitante" class="filter-label">Solicitante</label>
                            <select class="filter-select" id="solicitante" name="solicitante">
                                <option value="">Todos</option>
                                <?php if (isset($solicitantes) && is_array($solicitantes)): ?>
                                    <?php foreach ($solicitantes as $solicitante): ?>
                                        <option value="<?= $solicitante ?>" <?= (isset($filtros['solicitante']) && $filtros['solicitante'] == $solicitante) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($solicitante) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-column">
                            <label for="data_inicio" class="filter-label">Data Inicial</label>
                            <input type="date" class="filter-input" id="data_inicio" name="data_inicio" value="<?= isset($filtros['data_inicio']) ? $filtros['data_inicio'] : '' ?>">
                        </div>
                        <div class="filter-column">
                            <label for="data_fim" class="filter-label">Data Final</label>
                            <input type="date" class="filter-input" id="data_fim" name="data_fim" value="<?= isset($filtros['data_fim']) ? $filtros['data_fim'] : '' ?>">
                        </div>
                        <div class="filter-column">
                            <label for="busca" class="filter-label">Busca</label>
                            <input type="text" class="filter-input" id="busca" name="busca" value="<?= isset($filtros['busca']) && $filtros['busca'] !== null ? htmlspecialchars($filtros['busca']) : '' ?>" placeholder="Descrição, solicitante ou paciente">
                        </div>
                        <div class="filter-column">
                            <label for="ordenacao" class="filter-label">Ordenação</label>
                            <select class="filter-select" id="ordenacao" name="ordenacao">
                                <option value="recentes" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'recentes') || (!isset($filtros['ordenacao'])) ? 'selected' : '' ?>>Mais recentes</option>
                                <option value="antigos" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'antigos') ? 'selected' : '' ?>>Mais antigos</option>
                                <option value="status" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'status') ? 'selected' : '' ?>>Por status</option>
                                <option value="setor" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'setor') ? 'selected' : '' ?>>Por setor</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="filter-button primary">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <a href="<?= base_url('chamados/listar') ?>" class="filter-button secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de Chamados -->
    <div class="tickets-card">
        <div class="tickets-header">
            <h5 class="tickets-title">
                <i class="fas fa-list-ul me-2"></i>Solicitações
            </h5>
            <span class="tickets-count"><?= count($chamados) ?> chamados encontrados</span>
        </div>
        <div class="tickets-body">
            <?php if (!empty($chamados)): ?>
                <div class="table-responsive">
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Tipo de Serviço</th>
                                <th>Data de Solicitação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chamados as $chamado): ?>
                                <tr>
                                    <td class="ticket-id"><?= $chamado['id'] ?></td>
                                    <td>
                                        <div class="ticket-desc" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                            <?= htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') ?>
                                        </div>
                                    </td>
                                    <td class="ticket-user"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                    <td>
                                        <?php
                                        $setorEncontrado = false;
                                        foreach ($setores as $setorItem) {
                                            if ($setorItem['id'] == $chamado['setor_id']) {
                                                echo '<span class="ticket-setor">' . htmlspecialchars($setorItem['nome']) . '</span>';
                                                $setorEncontrado = true;
                                                break;
                                            }
                                        }
                                        if (!$setorEncontrado) {
                                            echo '<span class="ticket-setor na">N/A</span>';
                                        }
                                        ?>
                                    </td>
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
                                    <td>
                                        <?= !empty($chamado['tipo_servico']) ?
                                            '<span class="ticket-tipo">' . htmlspecialchars($chamado['tipo_servico']) . '</span>' :
                                            '<span class="ticket-tipo na">N/A</span>'
                                        ?>
                                    </td>
                                    <td class="ticket-date"><?= formatarData($chamado['data_solicitacao']) ?></td>
                                    <td>
                                        <div class="ticket-actions">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="action-btn view" data-bs-toggle="tooltip" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="action-btn edit" data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-tickets">
                    <div class="no-data-message">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Nenhum chamado encontrado com os filtros selecionados.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS Adicional para a Página de Chamados Integrada -->
<style>
    /* Estilos específicos para a Página de Chamados */
    .dashboard-container {
        padding: 1rem 0;
    }

    /* Estilos para o cabeçalho */
    .title-icon {
        color: #4361ee;
        font-size: 1.8rem;
        margin-right: 0.75rem;
    }

    .page-title {
        display: flex;
        align-items: center;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, #4361ee, #3a56d4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Estilos para os cards de estatísticas com cores ajustadas */
    .warning-licenses .stat-icon {
        background-color: var(--warning-light);
        color: var(--warning-color);
    }

    .info-licenses .stat-icon {
        background-color: var(--info-light);
        color: var(--info-color);
    }

    .success-licenses .stat-icon {
        background-color: var(--success-light);
        color: var(--success-color);
    }

    /* Estilos para o filtro avançado */
    .filter-advanced-card {
        background-color: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .filter-advanced-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .filter-advanced-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--light-gray);
        cursor: pointer;
    }

    .filter-advanced-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--dark-color);
        display: flex;
        align-items: center;
    }

    .filter-toggle-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background-color: var(--light-gray);
        color: var(--dark-color);
        border: none;
        border-radius: 50%;
        cursor: pointer;
        transition: var(--transition);
    }

    .filter-toggle-btn:hover {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }

    .filter-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: var(--white-color);
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .filter-advanced-body {
        padding: 1.5rem;
    }

    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }

    .filter-column {
        display: flex;
        flex-direction: column;
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

    .filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .filter-button {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        border: none;
        gap: 0.5rem;
    }

    .filter-button.primary {
        background-color: var(--primary-color);
        color: var(--white-color);
    }

    .filter-button.primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .filter-button.secondary {
        background-color: var(--light-gray);
        color: var(--dark-color);
    }

    .filter-button.secondary:hover {
        background-color: #dfe6e9;
        transform: translateY(-2px);
    }

    /* Estilos para a lista de chamados */
    .tickets-card {
        background-color: var(--white-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .tickets-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .tickets-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--light-gray);
    }

    .tickets-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        color: var(--dark-color);
        display: flex;
        align-items: center;
    }

    .tickets-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        background-color: var(--primary-color);
        color: var(--white-color);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .tickets-body {
        overflow: hidden;
    }

    .tickets-table {
        width: 100%;
        border-collapse: collapse;
    }

    .tickets-table th {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark-color);
        border-bottom: 1px solid var(--light-gray);
        white-space: nowrap;
    }

    .tickets-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--light-gray);
        vertical-align: middle;
    }

    .tickets-table tr:last-child td {
        border-bottom: none;
    }

    .tickets-table tr:hover {
        background-color: #f8f9fa;
    }

    .ticket-id {
        font-weight: 600;
        color: var(--primary-color);
    }

    .ticket-desc {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ticket-user {
        font-weight: 500;
    }

    .ticket-setor {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        background-color: var(--primary-light);
        color: var(--primary-color);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .ticket-tipo {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        background-color: var(--info-light);
        color: var(--info-color);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .ticket-setor.na,
    .ticket-tipo.na {
        background-color: var(--light-gray);
        color: var(--gray-color);
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
        white-space: nowrap;
    }

    .ticket-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        text-decoration: none;
        transition: var(--transition);
    }

    .action-btn.view {
        background-color: var(--primary-color);
        color: var(--white-color);
    }

    .action-btn.view:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .action-btn.edit {
        background-color: var(--warning-color);
        color: var(--white-color);
    }

    .action-btn.edit:hover {
        background-color: #e67e22;
        transform: translateY(-2px);
    }

    .no-tickets {
        padding: 3rem 1.5rem;
    }

    .no-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--gray-color);
    }

    .no-data-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .no-data-message p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* Responsividade */
    @media (max-width: 1199.98px) {
        .filter-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 991.98px) {
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

        .tickets-table th:nth-child(4),
        .tickets-table td:nth-child(4),
        .tickets-table th:nth-child(6),
        .tickets-table td:nth-child(6) {
            display: none;
        }
    }

    @media (max-width: 767.98px) {
        .filter-row {
            grid-template-columns: 1fr;
        }

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

        .tickets-table th:nth-child(2),
        .tickets-table td:nth-child(2),
        .tickets-table th:nth-child(7),
        .tickets-table td:nth-child(7) {
            display: none;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-button {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {

        .tickets-table th:nth-child(3),
        .tickets-table td:nth-child(3) {
            display: none;
        }

        .tickets-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
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

        // Torna o cabeçalho do filtro clicável para expandir/recolher
        document.querySelector('.filter-advanced-header').addEventListener('click', function() {
            var filtrosCollapse = bootstrap.Collapse.getInstance(document.getElementById('filtrosCollapse'));
            if (!filtrosCollapse) {
                filtrosCollapse = new bootstrap.Collapse(document.getElementById('filtrosCollapse'), {
                    toggle: false
                });
            }

            filtrosCollapse.toggle();

            // Alterna o ícone do botão
            var icon = this.querySelector('.filter-toggle-btn i');
            if (icon.classList.contains('fa-chevron-down')) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
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