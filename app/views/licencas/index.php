<div class="licencas-module">
    <div class="licenca-header">
        <div class="header-title">
            <h1><i class="fas fa-key"></i> Gerenciamento de Licenças</h1>
            <p class="subtitle">
                Gerencie as licenças de acesso para as empresas da sua organização
            </p>
        </div>




        <div class="licenca-controls">
            <div class="licenca-view-toggle">
                <button type="button" class="licenca-view-btn active" data-view="list" data-bs-toggle="tooltip" title="Visualizar em lista">
                    <i class="fas fa-list"></i>
                </button>
                <button type="button" class="licenca-view-btn" data-view="cards" data-bs-toggle="tooltip" title="Visualizar em cards">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>

            <div class="licenca-filter">
                <select id="filtro-status">
                    <option value="todos">Todos os status</option>
                    <option value="ativa">Ativas</option>
                    <option value="inativa">Inativas</option>
                    <option value="futura">Futuras</option>
                    <option value="expirada">Expiradas</option>
                </select>
            </div>

            <a href="<?= base_url('licencas/criar') ?>" class="licenca-add-btn">
                <i class="fas fa-plus"></i> Nova Licença
            </a>
        </div>
    </div>

    <!-- Visualização em tabela -->
    <div id="licencas-list" class="licenca-table-container">
        <table class="licenca-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Empresa</th>
                    <th width="15%">Quantidade</th>
                    <th width="30%">Período</th>
                    <th width="15%">Status</th>
                    <th width="10%">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($licencas) && !empty($licencas)): ?>
                    <?php foreach ($licencas as $licenca): ?>
                        <?php
                        $hoje = date('Y-m-d');
                        $status = 'Inativa';
                        $statusClass = 'inativa';
                        $statusIcon = 'fa-ban';
                        $statusData = 'inativa';

                        if ($licenca['ativo']) {
                            if ($licenca['data_inicio'] <= $hoje && $licenca['data_fim'] >= $hoje) {
                                $status = 'Ativa';
                                $statusClass = 'ativa';
                                $statusIcon = 'fa-check-circle';
                                $statusData = 'ativa';
                            } elseif ($licenca['data_inicio'] > $hoje) {
                                $status = 'Futura';
                                $statusClass = 'futura';
                                $statusIcon = 'fa-clock';
                                $statusData = 'futura';
                            } elseif ($licenca['data_fim'] < $hoje) {
                                $status = 'Expirada';
                                $statusClass = 'expirada';
                                $statusIcon = 'fa-exclamation-circle';
                                $statusData = 'expirada';
                            }
                        }

                        // Calcular duração em dias
                        $dataInicio = new DateTime($licenca['data_inicio']);
                        $dataFim = new DateTime($licenca['data_fim']);
                        $duracao = $dataInicio->diff($dataFim);
                        $duracaoDias = $duracao->days;
                        ?>
                        <tr class="licenca-item" data-status="<?= $statusData ?>">
                            <td><?= $licenca['id'] ?></td>
                            <td>
                                <div class="licenca-company">
                                    <i class="fas fa-building"></i>
                                    <?= $empresas[$licenca['empresa_id']]['nome'] ?? 'Empresa não encontrada' ?>
                                </div>
                            </td>
                            <td>
                                <span class="licenca-quantity"><?= $licenca['quantidade'] ?></span>
                            </td>
                            <td>
                                <div class="licenca-dates">
                                    <?= date('d/m/Y', strtotime($licenca['data_inicio'])) ?> até <?= date('d/m/Y', strtotime($licenca['data_fim'])) ?>
                                </div>
                                <div class="licenca-duration">
                                    <i class="fas fa-clock"></i> <?= $duracaoDias ?> dias
                                </div>
                            </td>
                            <td>
                                <span class="licenca-status <?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?>"></i>
                                    <?= $status ?>
                                </span>
                            </td>
                            <td>
                                <div class="licenca-actions">
                                    <a href="<?= base_url('licencas/editar/' . $licenca['id']) ?>" class="licenca-btn edit" data-bs-toggle="tooltip" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($licenca['ativo']): ?>
                                        <button type="button" class="licenca-btn delete" onclick="confirmarDesativacao(<?= $licenca['id'] ?>, '<?= $empresas[$licenca['empresa_id']]['nome'] ?? 'Empresa' ?>')" data-bs-toggle="tooltip" title="Desativar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= base_url('licencas/toggle/' . $licenca['id']) ?>" class="licenca-btn activate" data-bs-toggle="tooltip" title="Ativar">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="licenca-empty">
                                <div class="licenca-empty-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h3 class="licenca-empty-title">Nenhuma licença encontrada</h3>
                                <p class="licenca-empty-text">Você ainda não possui licenças cadastradas. Clique no botão abaixo para adicionar sua primeira licença.</p>
                                <a href="<?= base_url('licencas/criar') ?>" class="licenca-empty-btn">
                                    <i class="fas fa-plus"></i> Adicionar Licença
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Visualização em cards -->
    <div id="licencas-cards" class="licenca-cards d-none">
        <?php if (isset($licencas) && !empty($licencas)): ?>
            <?php foreach ($licencas as $licenca): ?>
                <?php
                $hoje = date('Y-m-d');
                $status = 'Inativa';
                $statusClass = 'inativa';
                $statusIcon = 'fa-ban';
                $cardClass = 'inativa';
                $statusData = 'inativa';

                if ($licenca['ativo']) {
                    if ($licenca['data_inicio'] <= $hoje && $licenca['data_fim'] >= $hoje) {
                        $status = 'Ativa';
                        $statusClass = 'ativa';
                        $statusIcon = 'fa-check-circle';
                        $cardClass = 'ativa';
                        $statusData = 'ativa';
                    } elseif ($licenca['data_inicio'] > $hoje) {
                        $status = 'Futura';
                        $statusClass = 'futura';
                        $statusIcon = 'fa-clock';
                        $cardClass = 'futura';
                        $statusData = 'futura';
                    } elseif ($licenca['data_fim'] < $hoje) {
                        $status = 'Expirada';
                        $statusClass = 'expirada';
                        $statusIcon = 'fa-exclamation-circle';
                        $cardClass = 'expirada';
                        $statusData = 'expirada';
                    }
                }

                // Calcular duração em dias
                $dataInicio = new DateTime($licenca['data_inicio']);
                $dataFim = new DateTime($licenca['data_fim']);
                $duracao = $dataInicio->diff($dataFim);
                $duracaoDias = $duracao->days;
                ?>
                <div class="licenca-item licenca-fade-in" data-status="<?= $statusData ?>">
                    <div class="licenca-card <?= $cardClass ?>">
                        <div class="licenca-card-header">
                            <div class="licenca-card-company">
                                <div class="licenca-card-company-name">
                                    <?= $empresas[$licenca['empresa_id']]['nome'] ?? 'Empresa não encontrada' ?>
                                </div>
                                <div class="licenca-card-company-id">
                                    ID: <?= $licenca['id'] ?>
                                </div>
                            </div>
                            <span class="licenca-card-status <?= $statusClass ?>">
                                <i class="fas <?= $statusIcon ?>"></i>
                                <?= $status ?>
                            </span>
                        </div>
                        <div class="licenca-card-body">
                            <div class="licenca-card-quantity">
                                <?= $licenca['quantidade'] ?>
                            </div>
                            <div class="licenca-card-quantity-label">
                                licença<?= $licenca['quantidade'] > 1 ? 's' : '' ?> disponíve<?= $licenca['quantidade'] > 1 ? 'is' : 'l' ?>
                            </div>

                            <div class="licenca-card-dates">
                                <div class="licenca-card-date">
                                    <span class="licenca-card-date-label">Início</span>
                                    <span class="licenca-card-date-value"><?= date('d/m/Y', strtotime($licenca['data_inicio'])) ?></span>
                                </div>
                                <div class="licenca-card-date-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div class="licenca-card-date">
                                    <span class="licenca-card-date-label">Fim</span>
                                    <span class="licenca-card-date-value"><?= date('d/m/Y', strtotime($licenca['data_fim'])) ?></span>
                                </div>
                            </div>

                            <div class="licenca-card-duration">
                                <i class="fas fa-clock"></i> Duração: <?= $duracaoDias ?> dias
                            </div>
                        </div>
                        <div class="licenca-card-footer">
                            <div class="licenca-card-actions">
                                <a href="<?= base_url('licencas/editar/' . $licenca['id']) ?>" class="licenca-card-btn edit" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($licenca['ativo']): ?>
                                    <button type="button" class="licenca-card-btn delete" onclick="confirmarDesativacao(<?= $licenca['id'] ?>, '<?= $empresas[$licenca['empresa_id']]['nome'] ?? 'Empresa' ?>')" data-bs-toggle="tooltip" title="Desativar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="<?= base_url('licencas/toggle/' . $licenca['id']) ?>" class="licenca-card-btn activate" data-bs-toggle="tooltip" title="Ativar">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="licenca-empty">
                <div class="licenca-empty-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3 class="licenca-empty-title">Nenhuma licença encontrada</h3>
                <p class="licenca-empty-text">Você ainda não possui licenças cadastradas. Clique no botão abaixo para adicionar sua primeira licença.</p>
                <a href="<?= base_url('licencas/criar') ?>" class="licenca-empty-btn">
                    <i class="fas fa-plus"></i> Adicionar Licença
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>