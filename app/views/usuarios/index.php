
<!-- Cabeçalho da página com design moderno e simplificado -->
<div class="users-dashboard">
    <!-- Cabeçalho com título e ações principais -->
    <div class="dashboard-header">
        <div class="header-title">
            <h1><i class="fas fa-users"></i> Gerenciamento de Usuários</h1>
            <p class="subtitle">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Visualizando usuários removidos do sistema' : 'Gerencie todos os usuários da sua organização' ?>
            </p>
        </div>
        <div class="header-actions">
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
            <a href="<?= base_url('usuarios/criar') ?>" class="btn-primary">
                <i class="fas fa-user-plus"></i>
                <span>Novo Usuário</span>
            </a>
        </div>
    </div>

    <!-- Alertas e notificações em um componente dedicado -->
    <div class="alerts-section">
        <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
            <div class="alert info">
                <div class="alert-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="alert-content">
                    <h5>Visualizando usuários removidos</h5>
                    <p>Estes usuários foram removidos do sistema, mas seus dados ainda estão armazenados. Você pode restaurá-los se necessário.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['usuario_removido_encontrado']) && $_SESSION['usuario_removido_encontrado']): ?>
            <div class="alert warning">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h5>Usuário removido encontrado!</h5>
                    <p>Detectamos que você está tentando criar um usuário com um e-mail que pertence a um usuário removido.</p>
                    <div class="alert-details">
                        <div class="user-details">
                            <p><strong>Nome:</strong> <?= $_SESSION['usuario_removido_nome'] ?></p>
                            <p><strong>E-mail:</strong> <?= $_SESSION['usuario_removido_email'] ?></p>
                            <p><strong>Removido em:</strong> <?= date('d/m/Y H:i', strtotime($_SESSION['usuario_removido_data'])) ?></p>
                        </div>
                        <div class="alert-actions">
                            <a href="<?= base_url('usuarios/confirmarRestauracao/' . $_SESSION['usuario_removido_id']) ?>" class="btn-success">
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

        <?php if (isset($licencasInfo) && $licencasInfo['disponiveis'] <= 0): ?>
            <div class="alert danger">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <h5>Limite de licenças atingido!</h5>
                    <p>Não há licenças disponíveis para criar novos usuários.</p>
                    <?php if (is_admin_master()): ?>
                        <div class="alert-actions">
                            <a href="<?= base_url('licencas/criar') ?>" class="btn-primary">
                                <i class="fas fa-plus-circle"></i> Criar Nova Licença
                            </a>
                        </div>
                    <?php else: ?>
                        <p>Por favor, solicite ao administrador master que crie novas licenças.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cards de estatísticas redesenhados -->
    <?php if (isset($licencasInfo)): ?>
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $licencasInfo['total'] ?></div>
                    <div class="stat-label">Total de Licenças</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon used">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $licencasInfo['utilizadas'] ?></div>
                    <div class="stat-label">Licenças Utilizadas</div>
                </div>
            </div>

            <!-- Card de Usuários Online Simplificado -->
            <div class="stat-card">
                <div class="stat-icon online">
                    <i class="fas fa-signal"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">
                        <div class="online-count">
                            <span class="online-indicator-dot"></span>
                            <?= $usuariosOnlineInfo['total'] ?>
                        </div>
                    </div>
                    <div class="stat-label">Usuários Online</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção principal com a lista de usuários -->
    <div class="users-main-section">
        <!-- Cabeçalho com título e opções de visualização -->
        <div class="section-header">
            <div class="section-title">
                <h2><?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Usuários Removidos' : 'Usuários Ativos' ?></h2>
                <?php if (isset($licencasInfo)): ?>
                    <div class="license-badge">
                        <i class="fas fa-users"></i>
                        <span><?= $licencasInfo['utilizadas'] ?> / <?= $licencasInfo['total'] ?> licenças utilizadas</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-options">
                <button id="cardViewBtn" class="view-btn active" title="Visualização em Cards">
                    <i class="fas fa-th-large"></i>
                </button>
                <button id="listViewBtn" class="view-btn" title="Visualização em Lista">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Filtros e pesquisa redesenhados -->
        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="userSearch" placeholder="Buscar por nome ou email...">
            </div>
            <div class="filter-options">
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <select id="statusFilter">
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
                    <select id="adminFilter">
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

                            <div class="card-header">
                                <div class="user-avatar-container">
                                    <div class="user-avatar <?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'admin-master' : 'admin') : 'user' ?>">
                                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                        <?php if (!empty($usuario['session_id'])): ?>
                                            <span class="online-indicator" title="Online desde <?= date('d/m/Y H:i', strtotime($usuario['session_start'])) ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-info">
                                    <h3 class="user-name"><?= $usuario['nome'] ?></h3>
                                    <p class="user-email"><?= $usuario['email'] ?></p>
                                </div>
                            </div>

                            <div class="card-tags">
                                <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                    <span class="tag removed">Removido</span>
                                <?php elseif ($usuario['ativo']): ?>
                                    <span class="tag active">Ativo</span>
                                <?php else: ?>
                                    <span class="tag inactive">Inativo</span>
                                <?php endif; ?>

                                <?php if ($usuario['admin']): ?>
                                    <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                        <span class="tag admin-master">Admin Master</span>
                                    <?php else: ?>
                                        <span class="tag admin">Admin</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!empty($usuario['cargo'])): ?>
                                    <span class="tag role"><?= $usuario['cargo'] ?></span>
                                <?php endif; ?>

                                <?php if (!empty($usuario['session_id'])): ?>
                                    <span class="tag online"><i class="fas fa-circle"></i> Online</span>
                                <?php else: ?>
                                    <span class="tag offline"><i class="fas fa-circle"></i> Offline</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-details">
                                <div class="detail-item">
                                    <span class="detail-label">Último Acesso</span>
                                    <span class="detail-value">
                                        <?php if ($usuario['ultimo_acesso']): ?>
                                            <i class="far fa-clock"></i>
                                            <?= isset($usuario['tempo_decorrido']) ? $usuario['tempo_decorrido'] : date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                        <?php else: ?>
                                            <i class="fas fa-ban"></i> Nunca
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <?php if (isset($mostrarRemovidos) && $mostrarRemovidos && isset($usuario['removido']) && $usuario['removido'] && isset($usuario['data_remocao'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Removido em</span>
                                        <span class="detail-value">
                                            <i class="far fa-calendar-times"></i>
                                            <?= date('d/m/Y H:i', strtotime($usuario['data_remocao'])) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($usuario['session_id']) && !empty($usuario['session_ip'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">IP</span>
                                        <span class="detail-value">
                                            <i class="fas fa-network-wired"></i>
                                            <?= $usuario['session_ip'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-actions">
                                <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                    <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                        <button type="button" class="btn-action restore"
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
                                    <a href="<?= base_url('usuarios/editar/' . $usuario['id']) ?>" class="btn-action edit">
                                        <i class="fas fa-edit"></i>
                                        <span>Editar</span>
                                    </a>

                                    <?php if ($usuario['id'] != get_user_id()): ?>
                                        <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                            <a href="<?= base_url('usuarios/toggle/' . $usuario['id']) ?>" class="btn-action <?= $usuario['ativo'] ? 'deactivate' : 'activate' ?>">
                                                <i class="fas <?= $usuario['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                                <span><?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?></span>
                                            </a>

                                            <button type="button" class="btn-action remove"
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
                                            <button type="button" class="btn-action end-session"
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
            <?php else: ?>
                <div class="no-results">
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
                                <div class="user-avatar <?= $usuario['admin'] ? ($usuario['admin_tipo'] == 'master' ? 'admin-master' : 'admin') : 'user' ?>">
                                    <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                    <?php if (!empty($usuario['session_id'])): ?>
                                        <span class="online-indicator" title="Online desde <?= date('d/m/Y H:i', strtotime($usuario['session_start'])) ?>"></span>
                                    <?php endif; ?>
                                </div>

                                <div class="user-list-info">
                                    <div class="user-list-name"><?= $usuario['nome'] ?></div>
                                    <div class="user-list-email"><?= $usuario['email'] ?></div>
                                </div>

                                <div class="user-list-tags">
                                    <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                        <span class="tag removed">Removido</span>
                                    <?php elseif ($usuario['ativo']): ?>
                                        <span class="tag active">Ativo</span>
                                    <?php else: ?>
                                        <span class="tag inactive">Inativo</span>
                                    <?php endif; ?>

                                    <?php if ($usuario['admin']): ?>
                                        <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                            <span class="tag admin-master">Admin Master</span>
                                        <?php else: ?>
                                            <span class="tag admin">Admin</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($usuario['cargo'])): ?>
                                        <span class="tag role"><?= $usuario['cargo'] ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($usuario['session_id'])): ?>
                                        <span class="tag online"><i class="fas fa-circle"></i> Online</span>
                                    <?php else: ?>
                                        <span class="tag offline"><i class="fas fa-circle"></i> Offline</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="user-list-actions">
                                <?php if (isset($usuario['removido']) && $usuario['removido']): ?>
                                    <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                        <button type="button" class="btn-icon restore"
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
                                    <a href="<?= base_url('usuarios/editar/' . $usuario['id']) ?>" class="btn-icon edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if ($usuario['id'] != get_user_id()): ?>
                                        <?php if (!$usuario['admin'] || is_admin_master()): ?>
                                            <a href="<?= base_url('usuarios/toggle/' . $usuario['id']) ?>" class="btn-icon <?= $usuario['ativo'] ? 'deactivate' : 'activate' ?>" title="<?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $usuario['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                            </a>

                                            <button type="button" class="btn-icon remove"
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
                                            <button type="button" class="btn-icon end-session"
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
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <p>Nenhum usuário <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginação - CORRIGIDA e APRIMORADA: Com tooltips e melhor feedback visual -->
        <?php if (isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Mostrando <?= count($usuarios) ?> de <?= $paginacao['total_registros'] ?> usuários
                </div>
                <ul class="pagination">
                    <?php if ($paginacao['pagina_atual'] > 1): ?>
                        <li>
                            <a href="<?= base_url('usuarios?pagina=1' . ($mostrarRemovidos ? '&mostrar_removidos=1' : '')) ?>"
                                class="pagination-link"
                                title="Primeira Página">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('usuarios?pagina=' . ($paginacao['pagina_atual'] - 1) . ($mostrarRemovidos ? '&mostrar_removidos=1' : '')) ?>"
                                class="pagination-link"
                                title="Página Anterior">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Botões desabilitados quando estiver na primeira página -->
                        <li>
                            <a href="javascript:void(0)"
                                class="pagination-link disabled"
                                title="Você está na primeira página">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"
                                class="pagination-link disabled"
                                title="Você está na primeira página">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    // Determina o intervalo de páginas a mostrar
                    $inicio = max(1, $paginacao['pagina_atual'] - 2);
                    $fim = min($paginacao['total_paginas'], $paginacao['pagina_atual'] + 2);

                    // Garante que pelo menos 5 páginas sejam mostradas, se disponíveis
                    if ($fim - $inicio + 1 < 5) {
                        if ($inicio == 1) {
                            $fim = min($paginacao['total_paginas'], $inicio + 4);
                        } elseif ($fim == $paginacao['total_paginas']) {
                            $inicio = max(1, $fim - 4);
                        }
                    }

                    // Exibe as páginas
                    for ($i = $inicio; $i <= $fim; $i++):
                        $isActive = $i == $paginacao['pagina_atual'];
                        $title = $isActive ? "Página Atual" : "Ir para Página $i";
                    ?>
                        <li>
                            <a href="<?= $isActive ? 'javascript:void(0)' : base_url('usuarios?pagina=' . $i . ($mostrarRemovidos ? '&mostrar_removidos=1' : '')) ?>"
                                class="pagination-link <?= $isActive ? 'active' : '' ?>"
                                title="<?= $title ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($paginacao['pagina_atual'] < $paginacao['total_paginas']): ?>
                        <li>
                            <a href="<?= base_url('usuarios?pagina=' . ($paginacao['pagina_atual'] + 1) . ($mostrarRemovidos ? '&mostrar_removidos=1' : '')) ?>"
                                class="pagination-link"
                                title="Próxima Página">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('usuarios?pagina=' . $paginacao['total_paginas'] . ($mostrarRemovidos ? '&mostrar_removidos=1' : '')) ?>"
                                class="pagination-link"
                                title="Última Página">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Botões desabilitados quando estiver na última página -->
                        <li>
                            <a href="javascript:void(0)"
                                class="pagination-link disabled"
                                title="Você está na última página">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"
                                class="pagination-link disabled"
                                title="Você está na última página">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Script para atualizar a barra de progresso da paginação -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Atualiza a barra de progresso da paginação
                    function updatePaginationProgress() {
                        const paginationContainer = document.querySelector('.pagination-container');
                        if (paginationContainer) {
                            const currentPage = <?= $paginacao['pagina_atual'] ?>;
                            const totalPages = <?= $paginacao['total_paginas'] ?>;
                            const progress = (currentPage / totalPages) * 100;
                            paginationContainer.style.setProperty('--progress', progress + '%');
                        }
                    }

                    // Atualiza a barra de progresso
                    updatePaginationProgress();
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<!-- Modais redesenhados -->
<div class="modal fade" id="removerModal" tabindex="-1" aria-labelledby="removerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removerModalLabel">Confirmar Remoção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-message">Tem certeza que deseja remover este usuário?</h5>
                <div class="modal-alert warning">
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
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRemover" class="btn-danger">Confirmar Remoção</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restaurarModal" tabindex="-1" aria-labelledby="restaurarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRestaurar" class="btn-success">Confirmar Restauração</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="encerrarSessaoModal" tabindex="-1" aria-labelledby="encerrarSessaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarEncerrarSessao" class="btn-danger">Encerrar Sessão</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Adicione este script no final da sua página ou em um arquivo JS separado
    document.addEventListener('DOMContentLoaded', function() {
        // Função para manter os filtros ao paginar
        function setupPaginationWithFilters() {
            const paginationLinks = document.querySelectorAll('.pagination-link');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Obtém os filtros atuais
                    const statusFilter = document.getElementById('statusFilter').value;
                    const adminFilter = document.getElementById('adminFilter').value;
                    const searchTerm = document.getElementById('userSearch').value;

                    if (statusFilter !== 'all' || adminFilter !== 'all' || searchTerm.trim() !== '') {
                        e.preventDefault();

                        // Constrói a URL com os filtros
                        let url = this.href;

                        // Adiciona os filtros à URL
                        if (statusFilter !== 'all') {
                            url += '&status=' + statusFilter;
                        }

                        if (adminFilter !== 'all') {
                            url += '&admin=' + adminFilter;
                        }

                        if (searchTerm.trim() !== '') {
                            url += '&search=' + encodeURIComponent(searchTerm.trim());
                        }

                        // Redireciona para a URL com filtros
                        window.location.href = url;
                    }
                });
            });
        }

        // Configura a paginação com filtros
        setupPaginationWithFilters();

        // Configura a alternância entre visualizações de cards e lista
        const cardViewBtn = document.getElementById('cardViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const cardView = document.getElementById('cardView');
        const listView = document.getElementById('listView');

        if (cardViewBtn && listViewBtn && cardView && listView) {
            cardViewBtn.addEventListener('click', function() {
                cardView.style.display = 'block';
                listView.style.display = 'none';
                cardViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                localStorage.setItem('userViewPreference', 'card');
            });

            listViewBtn.addEventListener('click', function() {
                cardView.style.display = 'none';
                listView.style.display = 'block';
                cardViewBtn.classList.remove('active');
                listViewBtn.classList.add('active');
                localStorage.setItem('userViewPreference', 'list');
            });

            // Restaura a preferência de visualização do usuário
            const viewPreference = localStorage.getItem('userViewPreference');
            if (viewPreference === 'list') {
                listViewBtn.click();
            }
        }

        // Configura a filtragem de usuários
        const userSearch = document.getElementById('userSearch');
        const statusFilter = document.getElementById('statusFilter');
        const adminFilter = document.getElementById('adminFilter');

        function filterUsers() {
            const searchTerm = userSearch.value.toLowerCase();
            const statusValue = statusFilter.value;
            const adminValue = adminFilter.value;

            // Filtra os cards de usuários
            const userCards = document.querySelectorAll('.user-card');
            userCards.forEach(card => {
                const userName = card.querySelector('.user-name').textContent.toLowerCase();
                const userEmail = card.querySelector('.user-email').textContent.toLowerCase();
                const userStatus = card.dataset.status;
                const userType = card.dataset.type;

                const matchesSearch = searchTerm === '' ||
                    userName.includes(searchTerm) ||
                    userEmail.includes(searchTerm);

                const matchesStatus = statusValue === 'all' || userStatus === statusValue;
                const matchesAdmin = adminValue === 'all' ||
                    (adminValue === 'admin' && userType === 'admin') ||
                    (adminValue === 'regular' && userType === 'regular');

                if (matchesSearch && matchesStatus && matchesAdmin) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Filtra os itens de lista de usuários
            const userListItems = document.querySelectorAll('.user-list-item');
            userListItems.forEach(item => {
                const userName = item.querySelector('.user-list-name').textContent.toLowerCase();
                const userEmail = item.querySelector('.user-list-email').textContent.toLowerCase();
                const userStatus = item.dataset.status;
                const userType = item.dataset.type;

                const matchesSearch = searchTerm === '' ||
                    userName.includes(searchTerm) ||
                    userEmail.includes(searchTerm);

                const matchesStatus = statusValue === 'all' || userStatus === statusValue;
                const matchesAdmin = adminValue === 'all' ||
                    (adminValue === 'admin' && userType === 'admin') ||
                    (adminValue === 'regular' && userType === 'regular');

                if (matchesSearch && matchesStatus && matchesAdmin) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            // Verifica se há resultados visíveis
            const visibleCards = document.querySelectorAll('.user-card[style="display: flex;"]');
            const visibleListItems = document.querySelectorAll('.user-list-item[style="display: flex;"]');

            const noResultsCard = document.querySelector('#cardView .no-results');
            const noResultsList = document.querySelector('#listView .no-results');

            if (visibleCards.length === 0 && noResultsCard) {
                noResultsCard.style.display = 'flex';
            } else if (noResultsCard) {
                noResultsCard.style.display = 'none';
            }

            if (visibleListItems.length === 0 && noResultsList) {
                noResultsList.style.display = 'flex';
            } else if (noResultsList) {
                noResultsList.style.display = 'none';
            }
        }

        if (userSearch && statusFilter && adminFilter) {
            userSearch.addEventListener('input', filterUsers);
            statusFilter.addEventListener('change', filterUsers);
            adminFilter.addEventListener('change', filterUsers);
        }

        // Configura os modais
        const removerModal = document.getElementById('removerModal');
        if (removerModal) {
            removerModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const email = button.getAttribute('data-email');

                document.getElementById('removerNome').textContent = nome;
                document.getElementById('removerEmail').textContent = email;
                document.getElementById('confirmarRemover').href = `<?= base_url('usuarios/remover/') ?>${id}`;
            });
        }

        const restaurarModal = document.getElementById('restaurarModal');
        if (restaurarModal) {
            restaurarModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const email = button.getAttribute('data-email');
                const data = button.getAttribute('data-data');

                document.getElementById('restaurarNome').textContent = nome;
                document.getElementById('restaurarEmail').textContent = email;
                document.getElementById('restaurarData').textContent = data;
                document.getElementById('confirmarRestaurar').href = `<?= base_url('usuarios/restaurar/') ?>${id}`;
            });
        }

        const encerrarSessaoModal = document.getElementById('encerrarSessaoModal');
        if (encerrarSessaoModal) {
            encerrarSessaoModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const email = button.getAttribute('data-email');
                const ip = button.getAttribute('data-ip');
                const data = button.getAttribute('data-data');

                document.getElementById('encerrarNome').textContent = nome;
                document.getElementById('encerrarEmail').textContent = email;
                document.getElementById('encerrarIP').textContent = ip;
                document.getElementById('encerrarData').textContent = data;
                document.getElementById('confirmarEncerrarSessao').href = `<?= base_url('usuarios/forcarLogout/') ?>${id}`;
            });
        }
    });
</script>