<!-- Cabeçalho da página com design moderno -->
<div class="users-container-header">
    <div class="header-content">
        <div class="title-section">
            <h1 class="page-title">
                <i class="fas fa-users title-icon"></i>
                Gerenciamento de Usuários
            </h1>
            <p class="subtitle">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Visualizando usuários removidos do sistema' : 'Gerencie todos os usuários da sua organização' ?>
            </p>
        </div>
        <div class="action-buttons">
            <div class="view-toggle">
                <a href="<?= base_url('usuarios') ?>" class="toggle-btn <?= !isset($mostrarRemovidos) || !$mostrarRemovidos ? 'active' : '' ?>">
                    <i class="fas fa-user-check"></i>
                    <span>Ativos</span>
                </a>
                <a href="<?= base_url('usuarios?mostrar_removidos=1') ?>" class="toggle-btn <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'active removed' : '' ?>">
                    <i class="fas fa-user-slash"></i>
                    <span>Removidos</span>
                </a>
            </div>
            <a href="<?= base_url('usuarios/criar') ?>" class="btn-new-user">
                <i class="fas fa-user-plus"></i>
                <span>Novo Usuário</span>
            </a>
        </div>
    </div>

    <!-- Alertas e notificações -->
    <div class="alerts-container">
        <!-- Alerta informativo quando estiver visualizando usuários removidos -->
        <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
            <div class="alert alert-info custom-alert info-alert">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Visualizando usuários removidos</h5>
                    <p class="alert-message">Estes usuários foram removidos do sistema, mas seus dados ainda estão armazenados. Você pode restaurá-los se necessário.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alerta quando um usuário removido é encontrado ao tentar criar um novo com o mesmo email -->
        <?php if (isset($_SESSION['usuario_removido_encontrado']) && $_SESSION['usuario_removido_encontrado']): ?>
            <div class="alert alert-warning custom-alert warning-alert">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Usuário removido encontrado!</h5>
                    <p class="alert-message">Detectamos que você está tentando criar um usuário com um e-mail que pertence a um usuário removido.</p>
                    <div class="alert-details">
                        <div class="user-details">
                            <p><strong>Nome:</strong> <?= $_SESSION['usuario_removido_nome'] ?></p>
                            <p><strong>E-mail:</strong> <?= $_SESSION['usuario_removido_email'] ?></p>
                            <p><strong>Removido em:</strong> <?= date('d/m/Y H:i', strtotime($_SESSION['usuario_removido_data'])) ?></p>
                        </div>
                        <div class="alert-actions">
                            <a href="<?= base_url('usuarios/confirmarRestauracao/' . $_SESSION['usuario_removido_id']) ?>" class="btn btn-success btn-restore">
                                <i class="fas fa-user-check"></i> Restaurar Usuário
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // Limpa as variáveis de sessão após exibir o alerta
            unset($_SESSION['usuario_removido_encontrado']);
            unset($_SESSION['usuario_removido_id']);
            unset($_SESSION['usuario_removido_nome']);
            unset($_SESSION['usuario_removido_email']);
            unset($_SESSION['usuario_removido_data']);
            ?>
        <?php endif; ?>

        <!-- Alerta quando o limite de licenças foi atingido -->
        <?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
            <div class="alert alert-danger custom-alert danger-alert">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Limite de licenças atingido!</h5>
                    <p class="alert-message">Não há licenças disponíveis para criar novos usuários.</p>
                    <?php if (is_admin_master()): ?>
                        <div class="alert-actions">
                            <a href="<?= base_url('licencas/criar') ?>" class="btn btn-primary btn-create-license">
                                <i class="fas fa-plus-circle"></i> Criar Nova Licença
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="alert-message">Por favor, solicite ao administrador master que crie novas licenças.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cards de resumo de licenças com design moderno -->
<?php if (isset($licencasInfo)): ?>
    <div class="license-stats">
        <!-- Card de Total de Licenças -->
        <div class="stat-card total-licenses">
            <div class="stat-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $licencasInfo['total'] ?></div>
                <div class="stat-label">Total de Licenças</div>
                <div class="stat-description">Licenças ativas no sistema</div>
            </div>
        </div>

        <!-- Card de Licenças Utilizadas -->
        <div class="stat-card used-licenses">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $licencasInfo['utilizadas'] ?></div>
                <div class="stat-label">Licenças Utilizadas</div>
                <div class="stat-description">Usuários ativos no sistema</div>
            </div>
        </div>

        <!-- Card de Licenças Disponíveis -->
        <div class="stat-card available-licenses <?= $licencasInfo['disponiveis'] > 0 ? 'has-licenses' : 'no-licenses' ?>">
            <div class="stat-icon">
                <i class="fas <?= $licencasInfo['disponiveis'] > 0 ? 'fa-unlock' : 'fa-lock' ?>"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $licencasInfo['disponiveis'] ?></div>
                <div class="stat-label">Licenças Disponíveis</div>
                <div class="stat-description">
                    <?= $licencasInfo['disponiveis'] > 0 ? 'Licenças restantes para uso' : 'Nenhuma licença disponível' ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Seção principal com a lista de usuários -->
<div class="card users-container">
    <div class="card-header users-header">
        <div class="header-left">
            <h2 class="section-title">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Usuários Removidos' : 'Usuários Ativos' ?>
            </h2>
            <?php if (isset($licencasInfo)): ?>
                <div class="badge license-badge">
                    <i class="fas fa-users"></i>
                    <span><?= $licencasInfo['utilizadas'] ?> / <?= $licencasInfo['total'] ?> licenças utilizadas</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="view-options">
            <button id="cardViewBtn" class="btn view-btn active" title="Visualização em Cards">
                <i class="fas fa-th-large"></i>
            </button>
            <button id="listViewBtn" class="btn view-btn" title="Visualização em Lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Filtro e pesquisa de usuários -->
    <div class="card-body p-0">
        <div class="users-filters">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="userSearch" class="form-control search-input" placeholder="Buscar usuários...">
            </div>
            <div class="filter-options">
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <select id="statusFilter" class="form-select filter-select">
                        <option value="all">Todos</option>
                        <option value="active">Ativos</option>
                        <option value="inactive">Inativos</option>
                        <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                            <option value="removed">Removidos</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="adminFilter">Tipo:</label>
                    <select id="adminFilter" class="form-select filter-select">
                        <option value="all">Todos</option>
                        <option value="admin">Administradores</option>
                        <option value="regular">Usuários Regulares</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Visualização em Cards (padrão) -->
        <div id="cardView" class="users-cards-view">
            <?php if (isset($usuarios) && !empty($usuarios)): ?>
                <div class="users-cards">
                    <?php foreach ($usuarios as $usuario): ?>
                        <div class="user-card <?= isset($usuario['removido']) && $usuario['removido'] ? 'removed' : ($usuario['ativo'] ? 'active' : 'inactive') ?>"
                            data-status="<?= isset($usuario['removido']) && $usuario['removido'] ? 'removed' : ($usuario['ativo'] ? 'active' : 'inactive') ?>"
                            data-type="<?= $usuario['admin'] ? 'admin' : 'regular' ?>">

                            <!-- Barra de status no topo do card -->
                            <div class="card-status-bar"></div>

                            <!-- Cabeçalho do card com avatar e informações principais -->
                            <div class="user-card-header">
                                <div class="user-avatar-wrapper">
                                    <div class="user-avatar bg-<?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'danger' : 'primary') : 'secondary' ?>">
                                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                    </div>
                                    <?php if (!empty($usuario['session_id'])): ?>
                                        <div class="online-indicator" title="Online desde <?= date('d/m/Y H:i', strtotime($usuario['session_start'])) ?>"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="user-primary-info">
                                    <h3 class="user-name"><?= $usuario['nome'] ?></h3>
                                    <p class="user-email"><?= $usuario['email'] ?></p>

                                    <!-- Tags/Flags para informações importantes -->
                                    <div class="user-tags">
                                        <!-- Status -->
                                        <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                            <span class="badge user-tag tag-status removed">Removido</span>
                                        <?php elseif ($usuario['ativo']): ?>
                                            <span class="badge user-tag tag-status active">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge user-tag tag-status inactive">Inativo</span>
                                        <?php endif; ?>

                                        <!-- Tipo de Usuário -->
                                        <?php if ($usuario['admin']): ?>
                                            <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                                <span class="badge user-tag tag-admin master">Admin Master</span>
                                            <?php else: ?>
                                                <span class="badge user-tag tag-admin">Admin</span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Cargo (se existir) -->
                                        <?php if (!empty($usuario['cargo'])): ?>
                                            <span class="badge user-tag tag-role"><?= $usuario['cargo'] ?></span>
                                        <?php endif; ?>

                                        <!-- Status da Sessão -->
                                        <?php if (!empty($usuario['session_id'])): ?>
                                            <span class="badge user-tag tag-session online"><i class="fas fa-circle"></i> Online</span>
                                        <?php else: ?>
                                            <span class="badge user-tag tag-session offline"><i class="fas fa-circle"></i> Offline</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Corpo do card com informações detalhadas -->
                            <div class="user-card-body">
                                <!-- Último acesso -->
                                <div class="info-item">
                                    <div class="info-label">Último Acesso</div>
                                    <div class="info-value">
                                        <?php if ($usuario['ultimo_acesso']): ?>
                                            <i class="far fa-clock"></i>
                                            <?= isset($usuario['tempo_decorrido']) ? $usuario['tempo_decorrido'] : date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                        <?php else: ?>
                                            <i class="fas fa-ban"></i> Nunca
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Data de remoção (se aplicável) -->
                                <?php if (isset($mostrarRemovidos) && $mostrarRemovidos && isset($usuario['removido']) && $usuario['removido'] && isset($usuario['data_remocao'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Removido em</div>
                                        <div class="info-value">
                                            <i class="far fa-calendar-times"></i>
                                            <?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- IP da Sessão (se online) -->
                                <?php if (!empty($usuario['session_id']) && !empty($usuario['session_ip'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">IP</div>
                                        <div class="info-value">
                                            <i class="fas fa-network-wired"></i>
                                            <?= $usuario['session_ip'] ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Rodapé do card com ações -->
                            <div class="user-card-footer">
                                <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                    <!-- Botão de restauração para usuários removidos -->
                                    <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                        <button type="button" class="btn btn-action restore"
                                            data-bs-toggle="modal"
                                            data-bs-target="#restaurarModal"
                                            data-id="<?= $usuario['id'] ?>"
                                            data-nome="<?= $usuario['nome'] ?>"
                                            data-email="<?= $usuario['email'] ?>"
                                            data-data="<?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>">
                                            <i class="fas fa-user-check"></i>
                                            <span>Restaurar</span>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Botões para usuários não removidos -->
                                    <a href="<?= base_url('usuarios/editar/' . $usuario['id']) ?>" class="btn btn-action edit">
                                        <i class="fas fa-edit"></i>
                                        <span>Editar</span>
                                    </a>

                                    <?php if ($usuario['id'] != get_user_id()): ?>
                                        <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                            <a href="<?= base_url('usuarios/toggle/' . $usuario['id']) ?>" class="btn btn-action <?= $usuario['ativo'] ? 'deactivate' : 'activate' ?>">
                                                <i class="fas <?= $usuario['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                                <span><?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?></span>
                                            </a>

                                            <button type="button" class="btn btn-action remove"
                                                data-bs-toggle="modal"
                                                data-bs-target="#removerModal"
                                                data-id="<?= $usuario['id'] ?>"
                                                data-nome="<?= $usuario['nome'] ?>"
                                                data-email="<?= $usuario['email'] ?>">
                                                <i class="fas fa-trash"></i>
                                                <span>Remover</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (!empty($usuario['session_id'])): ?>
                                            <button type="button" class="btn btn-action end-session"
                                                data-bs-toggle="modal"
                                                data-bs-target="#encerrarSessaoModal"
                                                data-id="<?= $usuario['id'] ?>"
                                                data-nome="<?= $usuario['nome'] ?>"
                                                data-email="<?= $usuario['email'] ?>"
                                                data-ip="<?= $usuario['session_ip'] ?>"
                                                data-data="<?= date('d/m/Y H:i', strtotime($usuario['session_start'])) ?>">
                                                <i class="fas fa-sign-out-alt"></i>
                                                <span>Encerrar</span>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Container de paginação será adicionado via JavaScript -->
            <?php else: ?>
                <div class="no-users-found">
                    <i class="fas fa-search"></i>
                    <p>Nenhum usuário <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Visualização em Lista (simplificada) -->
        <div id="listView" class="users-list-view" style="display: none;">
            <?php if (isset($usuarios) && !empty($usuarios)): ?>
                <div class="users-list">
                    <?php foreach ($usuarios as $usuario): ?>
                        <div class="user-list-item <?= isset($usuario['removido']) && $usuario['removido'] ? 'removed' : ($usuario['ativo'] ? 'active' : 'inactive') ?>"
                            data-status="<?= isset($usuario['removido']) && $usuario['removido'] ? 'removed' : ($usuario['ativo'] ? 'active' : 'inactive') ?>"
                            data-type="<?= $usuario['admin'] ? 'admin' : 'regular' ?>">
                            <div class="user-list-main">
                                <div class="user-avatar bg-<?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'danger' : 'primary') : 'secondary' ?>">
                                    <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                </div>
                                <div class="user-list-info">
                                    <div class="user-list-name"><?= $usuario['nome'] ?></div>
                                    <div class="user-list-email"><?= $usuario['email'] ?></div>
                                </div>

                                <!-- Tags para a visualização em lista -->
                                <div class="user-list-tags">
                                    <!-- Status -->
                                    <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                        <span class="badge user-tag tag-status removed">Removido</span>
                                    <?php elseif ($usuario['ativo']): ?>
                                        <span class="badge user-tag tag-status active">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge user-tag tag-status inactive">Inativo</span>
                                    <?php endif; ?>

                                    <!-- Tipo de Usuário -->
                                    <?php if ($usuario['admin']): ?>
                                        <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                            <span class="badge user-tag tag-admin master">Admin Master</span>
                                        <?php else: ?>
                                            <span class="badge user-tag tag-admin">Admin</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Cargo (se existir) -->
                                    <?php if (!empty($usuario['cargo'])): ?>
                                        <span class="badge user-tag tag-role"><?= $usuario['cargo'] ?></span>
                                    <?php endif; ?>

                                    <!-- Status da Sessão -->
                                    <?php if (!empty($usuario['session_id'])): ?>
                                        <span class="badge user-tag tag-session online"><i class="fas fa-circle"></i> Online</span>
                                    <?php else: ?>
                                        <span class="badge user-tag tag-session offline"><i class="fas fa-circle"></i> Offline</span>
                                    <?php endif; ?>

                                    <!-- Último acesso -->
                                    <?php if ($usuario['ultimo_acesso']): ?>
                                        <span class="badge user-tag">
                                            <i class="far fa-clock"></i>
                                            <?= isset($usuario['tempo_decorrido']) ? $usuario['tempo_decorrido'] : date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="user-list-actions">
                                <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                    <!-- Botão de restauração para usuários removidos -->
                                    <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                        <button type="button" class="btn btn-action restore"
                                            data-bs-toggle="modal"
                                            data-bs-target="#restaurarModal"
                                            data-id="<?= $usuario['id'] ?>"
                                            data-nome="<?= $usuario['nome'] ?>"
                                            data-email="<?= $usuario['email'] ?>"
                                            data-data="<?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>"
                                            title="Restaurar">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Botões para usuários não removidos -->
                                    <a href="<?= base_url('usuarios/editar/' . $usuario['id']) ?>" class="btn btn-action edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if ($usuario['id'] != get_user_id()): ?>
                                        <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                            <a href="<?= base_url('usuarios/toggle/' . $usuario['id']) ?>" class="btn btn-action <?= $usuario['ativo'] ? 'deactivate' : 'activate' ?>" title="<?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $usuario['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                            </a>

                                            <button type="button" class="btn btn-action remove"
                                                data-bs-toggle="modal"
                                                data-bs-target="#removerModal"
                                                data-id="<?= $usuario['id'] ?>"
                                                data-nome="<?= $usuario['nome'] ?>"
                                                data-email="<?= $usuario['email'] ?>"
                                                title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (!empty($usuario['session_id'])): ?>
                                            <button type="button" class="btn btn-action end-session"
                                                data-bs-toggle="modal"
                                                data-bs-target="#encerrarSessaoModal"
                                                data-id="<?= $usuario['id'] ?>"
                                                data-nome="<?= $usuario['nome'] ?>"
                                                data-email="<?= $usuario['email'] ?>"
                                                data-ip="<?= $usuario['session_ip'] ?>"
                                                data-data="<?= date('d/m/Y H:i', strtotime($usuario['session_start'])) ?>"
                                                title="Encerrar Sessão">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-users-found">
                    <i class="fas fa-search"></i>
                    <p>Nenhum usuário <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Remoção com design moderno -->
<div class="modal fade" id="removerModal" tabindex="-1" aria-labelledby="removerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="removerModalLabel">Confirmar Remoção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-message">Tem certeza que deseja remover este usuário?</h5>
                <div class="modal-alert">
                    <i class="fas fa-info-circle"></i>
                    <p>O usuário será marcado como removido, mas seus dados permanecerão no sistema. Você poderá restaurá-lo posteriormente se necessário.</p>
                </div>
                <div class="modal-details">
                    <div class="detail-item">
                        <span class="detail-label">Nome:</span>
                        <span class="detail-value" id="removerNome"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">E-mail:</span>
                        <span class="detail-value" id="removerEmail"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRemover" class="btn btn-danger btn-confirm danger">Confirmar Remoção</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Restauração com design moderno -->
<div class="modal fade" id="restaurarModal" tabindex="-1" aria-labelledby="restaurarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="restaurarModalLabel">Confirmar Restauração</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon success">
                    <i class="fas fa-user-check"></i>
                </div>
                <h5 class="modal-message">Tem certeza que deseja restaurar este usuário?</h5>
                <div class="modal-alert info">
                    <i class="fas fa-info-circle"></i>
                    <p>O usuário será restaurado e poderá acessar o sistema novamente.</p>
                </div>
                <div class="modal-details">
                    <div class="detail-item">
                        <span class="detail-label">Nome:</span>
                        <span class="detail-value" id="restaurarNome"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">E-mail:</span>
                        <span class="detail-value" id="restaurarEmail"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Removido em:</span>
                        <span class="detail-value" id="restaurarData"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRestaurar" class="btn btn-success btn-confirm success">Confirmar Restauração</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Encerrar Sessão com design moderno -->
<div class="modal fade" id="encerrarSessaoModal" tabindex="-1" aria-labelledby="encerrarSessaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="encerrarSessaoModalLabel">Encerrar Sessão do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon danger">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h5 class="modal-message">Tem certeza que deseja encerrar a sessão deste usuário?</h5>
                <div class="modal-alert warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>O usuário será desconectado imediatamente e perderá qualquer trabalho não salvo.</p>
                </div>
                <div class="modal-details">
                    <div class="detail-item">
                        <span class="detail-label">Nome:</span>
                        <span class="detail-value" id="encerrarNome"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">E-mail:</span>
                        <span class="detail-value" id="encerrarEmail"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">IP:</span>
                        <span class="detail-value" id="encerrarIP"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Logado desde:</span>
                        <span class="detail-value" id="encerrarData"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarEncerrarSessao" class="btn btn-danger btn-confirm danger">Encerrar Sessão</a>
            </div>
        </div>
    </div>
</div>