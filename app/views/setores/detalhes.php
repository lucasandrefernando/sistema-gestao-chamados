<?php

/**
 * Detalhes de um setor específico
 * Este arquivo exibe informações detalhadas, chamados e usuários de um setor
 * 
 * @version 1.1
 * @author Desenvolvedor
 * 
 * NOTAS PARA DESENVOLVEDORES:
 * - Este arquivo é responsável pela visualização de detalhes de um setor específico
 * - Ele exibe três abas: Chamados, Usuários e Informações
 * - O cabeçalho foi redesenhado para seguir o padrão visual do sistema
 * - A navegação entre abas é feita com Bootstrap 5 Tabs
 * - Os dados são carregados dinamicamente via JavaScript para algumas seções
 */

// Título da página
$pageTitle = 'Detalhes do Setor - ' . $setor['nome'];
?>

<div class="setor-container">
    <!-- Cabeçalho do Setor (Redesenhado conforme solicitado) -->
    <div class="setor-header">
        <div class="setor-header-content">
            <div class="setor-titulo-secao">
                <h1 class="setor-titulo">
                    <i class="fas fa-building setor-icone"></i>
                    <?= htmlspecialchars($setor['nome']) ?>
                </h1>
                <p class="setor-subtitulo">
                    <?php if (!empty($setor['descricao'])): ?>
                        <?= nl2br(htmlspecialchars(substr($setor['descricao'], 0, 120))) ?>
                        <?= strlen($setor['descricao']) > 120 ? '...' : '' ?>
                    <?php else: ?>
                        Detalhes e informações do setor
                    <?php endif; ?>
                </p>
            </div>
            <div class="setor-acoes">
                <!-- Botão de voltar que usa window.history.back() -->
                <button onclick="window.history.back()" class="setor-btn-voltar">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </button>
            </div>
        </div>
        <div class="setor-status-badge">
            <span class="status-indicator <?= $setor['ativo'] ? 'active' : 'inactive' ?>">
                <i class="fas <?= $setor['ativo'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
            </span>
        </div>
    </div>

    <!-- Navegação por abas -->
    <ul class="nav nav-tabs-custom" id="setorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="chamados-tab" data-bs-toggle="tab" data-bs-target="#chamados" type="button" role="tab" aria-controls="chamados" aria-selected="true">
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
    <div class="tab-content" id="setorTabsContent">
        <!-- Aba Chamados -->
        <div class="tab-pane fade show active" id="chamados" role="tabpanel" aria-labelledby="chamados-tab">
            <div class="content-card">
                <div class="content-card-header">
                    <h5><i class="fas fa-ticket-alt"></i> Chamados do Setor</h5>
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="chamadosSearch" class="form-control" placeholder="Buscar chamados...">
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
                                            <td data-label="ID"><?= isset($chamado['id']) ? $chamado['id'] : 'N/A' ?></td>
                                            <td data-label="Descrição" class="text-truncate" style="max-width: 250px;" title="<?= isset($chamado['titulo']) ? htmlspecialchars($chamado['titulo']) : (isset($chamado['descricao']) ? htmlspecialchars($chamado['descricao']) : 'N/A') ?>">
                                                <?= isset($chamado['titulo']) ? htmlspecialchars($chamado['titulo']) : (isset($chamado['descricao']) ? htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') : 'N/A') ?>
                                            </td>
                                            <td data-label="Solicitante"><?= isset($chamado['solicitante_nome']) ? htmlspecialchars($chamado['solicitante_nome']) : (isset($chamado['solicitante']) ? htmlspecialchars($chamado['solicitante']) : 'N/A') ?></td>
                                            <td data-label="Status">
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
                                            <td data-label="Data"><?= isset($chamado['data_abertura']) ? formatarData($chamado['data_abertura']) : (isset($chamado['data_solicitacao']) ? formatarData($chamado['data_solicitacao']) : 'N/A') ?></td>
                                            <td data-label="Ações" class="text-center">
                                                <div class="btn-action-group">
                                                    <?php if (isset($chamado['id'])): ?>
                                                        <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-action btn-view" data-bs-toggle="tooltip" title="Visualizar chamado">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-action btn-view" disabled>
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="pagination-container" id="chamadosPagination">
                            <!-- Será preenchido via JavaScript -->
                        </div>

                        <div id="noResults" class="text-center py-5" style="display: none;">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum chamado encontrado para a busca.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum chamado encontrado para este setor.</p>
                            <a href="<?= base_url('chamados/criar?setor_id=' . $setor['id']) ?>" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-1"></i> Criar Primeiro Chamado
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Aba Usuários -->
        <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
            <div class="content-card">
                <div class="content-card-header">
                    <h5><i class="fas fa-users"></i> Usuários com Acesso ao Setor</h5>
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="usuariosSearch" class="form-control" placeholder="Buscar usuários...">
                    </div>
                </div>
                <div class="content-card-body">
                    <div id="usuariosContainer" class="user-grid">
                        <!-- Carregando usuários -->
                        <div class="loading-container">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2 text-muted">Carregando usuários...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba Informações (Removido o card de informações redundantes) -->
        <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
            <!-- Cards de informações -->
            <div class="info-cards">
                <!-- Card de Descrição -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-align-left"></i>
                        <h4>Descrição</h4>
                    </div>
                    <div class="info-card-body">
                        <?php if (!empty($setor['descricao'])): ?>
                            <div class="setor-descricao">
                                <?= nl2br(htmlspecialchars($setor['descricao'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>Este setor não possui descrição.</p>
                                <?php if (is_admin()): ?>
                                    <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-edit me-1"></i> Adicionar Descrição
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card de Estatísticas -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-chart-pie"></i>
                        <h4>Estatísticas</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="stats-grid">
                            <!-- Chamados Abertos -->
                            <div class="stat-item">
                                <div class="stat-icon" style="background-color: #ffc107;">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-value"><?= $estatisticas['chamados_abertos'] ?></span>
                                    <span class="stat-label">Chamados Abertos</span>
                                </div>
                            </div>

                            <!-- Chamados em Atendimento -->
                            <div class="stat-item">
                                <div class="stat-icon" style="background-color: #0d6efd;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-value"><?= $estatisticas['chamados_em_atendimento'] ?></span>
                                    <span class="stat-label">Em Atendimento</span>
                                </div>
                            </div>

                            <!-- Tempo Médio -->
                            <div class="stat-item">
                                <div class="stat-icon" style="background-color: #6f42c1;">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-value"><?= formatarTempo($estatisticas['tempo_medio_atendimento']) ?></span>
                                    <span class="stat-label">Tempo Médio</span>
                                </div>
                            </div>

                            <!-- Total de Chamados -->
                            <div class="stat-item">
                                <div class="stat-icon" style="background-color: #198754;">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-value"><?= $estatisticas['total_chamados'] ?></span>
                                    <span class="stat-label">Total de Chamados</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Detalhes Adicionais -->
                <div class="info-card">
                    <div class="info-card-header">
                        <i class="fas fa-info-circle"></i>
                        <h4>Detalhes Adicionais</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="details-list">
                            <?php if (isset($setor['atualizado_em']) || isset($setor['data_atualizacao'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-edit"></i>
                                        Última Atualização
                                    </div>
                                    <div class="detail-value">
                                        <?= isset($setor['atualizado_em']) ? formatarData($setor['atualizado_em']) : formatarData($setor['data_atualizacao']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($setor['criado_por'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-user"></i>
                                        Criado Por
                                    </div>
                                    <div class="detail-value">
                                        <?= isset($setor['criado_por_nome']) ? htmlspecialchars($setor['criado_por_nome']) : 'ID: ' . $setor['criado_por'] ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($statusMaisComum) && isset($statusMaisComum['nome'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-tag"></i>
                                        Status Mais Comum
                                    </div>
                                    <div class="detail-value">
                                        <?= htmlspecialchars($statusMaisComum['nome']) ?>
                                        (<?= $statusMaisComum['porcentagem'] ?>%)
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($mesMaisChamados) && isset($mesMaisChamados['nome'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-calendar-check"></i>
                                        Mês Mais Ativo
                                    </div>
                                    <div class="detail-value">
                                        <?= htmlspecialchars($mesMaisChamados['nome']) ?>
                                        (<?= $mesMaisChamados['total'] ?> chamados)
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Informações básicas do setor -->
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-id-card"></i>
                                    ID do Setor
                                </div>
                                <div class="detail-value">
                                    <?= $setor['id'] ?>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Data de Criação
                                </div>
                                <div class="detail-value">
                                    <?= isset($setor['criado_em']) ? formatarData($setor['criado_em']) : (isset($setor['data_criacao']) ? formatarData($setor['data_criacao']) : 'N/A') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Funções auxiliares para formatação de status por ID
    function getStatusColorById(statusId) {
        const colors = {
            1: '1', // Aberto - Amarelo
            2: '2', // Em Atendimento - Azul
            3: '3', // Pausado - Roxo
            4: '4', // Concluído - Verde
            5: '5' // Cancelado - Preto
        };
        return colors[statusId] || 'secondary';
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