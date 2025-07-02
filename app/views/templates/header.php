<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestão de Chamados - Controle e acompanhamento de solicitações">
    <meta name="author" content="Lucas André Fernando">
    <title><?= APP_NAME ?><?= isset($page_title) ? ' - ' . $page_title : '' ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('public/img/favicon.ico') ?>" type="image/x-icon">

    <!-- Fontes e Frameworks -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('public/css/main.css') ?>">

    <!-- Definição de variáveis JavaScript -->
    <script>
        var BASE_URL = '<?= base_url() ?>';
    </script>
</head>

<body>
    <?php if (is_authenticated()): ?>
        <!-- Navbar Principal -->
        <nav class="navbar navbar-expand-lg fixed-top app-navbar">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <!-- Seção Esquerda - Nome do Hospital -->
                    <div class="navbar-left">
                        <a href="<?= base_url('dashboard') ?>" class="navbar-brand">
                            <span class="brand-text">Hospital Madre Teresa</span>
                        </a>
                    </div>

                    <!-- Seção Central - Título do Sistema -->
                    <div class="navbar-center">
                        <h1 class="system-title">Gestão de Chamados</h1>
                    </div>

                    <!-- Seção Direita - Ações e Perfil -->
                    <div class="navbar-right">
                        <!-- Notificações -->
                        <?php
                        // Buscar notificações não lidas
                        $notificacoes = [];
                        $total_notificacoes = 0;

                        if (is_authenticated() && class_exists('Notificacao')) {
                            $notificacaoModel = new Notificacao();
                            $notificacoes = $notificacaoModel->buscarNotificacoesFormatadas(get_user_id());
                            $total_notificacoes = $notificacaoModel->contarNotificacoesNaoLidas(get_user_id());
                        }
                        ?>
                        <div class="nav-item dropdown">
                            <button class="btn-icon notification-toggle" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if ($total_notificacoes > 0): ?>
                                    <span class="badge-counter"><?= $total_notificacoes > 99 ? '99+' : $total_notificacoes ?></span>
                                <?php endif; ?>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                <div class="dropdown-header">
                                    <h6>Notificações</h6>
                                    <?php if ($total_notificacoes > 0): ?>
                                        <button class="btn-text mark-all-read">Marcar todas como lidas</button>
                                    <?php endif; ?>
                                </div>

                                <div class="dropdown-body">
                                    <?php if (empty($notificacoes)): ?>
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-bell-slash"></i>
                                            </div>
                                            <p>Não há notificações no momento</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="notification-list">
                                            <?php foreach ($notificacoes as $notificacao): ?>
                                                <div class="notification-item <?= isset($notificacao['lida']) && $notificacao['lida'] ? 'read' : 'unread' ?>" data-id="<?= $notificacao['id'] ?>">
                                                    <div class="notification-icon bg-<?= $notificacao['cor'] ?>">
                                                        <i class="<?= $notificacao['icone'] ?>"></i>
                                                    </div>
                                                    <div class="notification-content">
                                                        <div class="notification-title"><?= htmlspecialchars($notificacao['titulo']) ?></div>
                                                        <div class="notification-message"><?= htmlspecialchars($notificacao['descricao'] ?? $notificacao['mensagem'] ?? '') ?></div>
                                                        <div class="notification-meta">
                                                            <span class="notification-time"><?= $notificacao['tempo'] ?></span>
                                                        </div>
                                                        <div class="notification-actions">
                                                            <?php if ($notificacao['referencia_tipo'] == 'chamado' && $notificacao['referencia_id']): ?>
                                                                <a href="<?= base_url('chamados/visualizar/' . $notificacao['referencia_id']) ?>" class="btn-action btn-primary">Ver</a>
                                                            <?php endif; ?>
                                                            <?php if (!isset($notificacao['lida']) || !$notificacao['lida']): ?>
                                                                <button class="btn-action" data-action="dismiss" data-id="<?= $notificacao['id'] ?>">Ignorar</button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="dropdown-footer">
                                    <a href="<?= base_url('notificacoes') ?>" class="btn-link">Ver todas as notificações</a>
                                </div>
                            </div>
                        </div>

                        <!-- Ações Rápidas -->
                        <div class="nav-item dropdown">
                            <button class="btn-icon" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end quick-actions-dropdown" aria-labelledby="quickActionsDropdown">
                                <div class="dropdown-header">
                                    <h6>Ações Rápidas</h6>
                                </div>

                                <div class="dropdown-body">
                                    <div class="quick-actions-grid">
                                        <a href="<?= base_url('chamados/criar') ?>" class="quick-action-item">
                                            <div class="quick-action-icon bg-primary">
                                                <i class="fas fa-ticket-alt"></i>
                                            </div>
                                            <span>Novo Chamado</span>
                                        </a>

                                        <a href="<?= base_url('chamados/listar') ?>" class="quick-action-item">
                                            <div class="quick-action-icon bg-info">
                                                <i class="fas fa-search"></i>
                                            </div>
                                            <span>Buscar</span>
                                        </a>

                                        <a href="<?= base_url('dashboard') ?>" class="quick-action-item">
                                            <div class="quick-action-icon bg-success">
                                                <i class="fas fa-tachometer-alt"></i>
                                            </div>
                                            <span>Dashboard</span>
                                        </a>

                                        <a href="<?= base_url('chamados/meus') ?>" class="quick-action-item">
                                            <div class="quick-action-icon bg-warning">
                                                <i class="fas fa-user-tag"></i>
                                            </div>
                                            <span>Meus Chamados</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Perfil do Usuário -->
                        <div class="nav-item dropdown">
                            <?php
                            // Obter informações do usuário
                            $user_id = $_SESSION['user_id'] ?? 0;
                            $user_name = $_SESSION['user_name'] ?? 'Usuário';
                            $user_role = $_SESSION['user_role'] ?? 'Usuário';
                            $user_email = $_SESSION['user_email'] ?? '';

                            // Buscar email se não estiver na sessão
                            if (empty($user_email) && $user_id > 0 && class_exists('Usuario')) {
                                $usuarioModel = new Usuario();
                                $usuario = $usuarioModel->findById($user_id);
                                if ($usuario && isset($usuario['email'])) {
                                    $user_email = $usuario['email'];
                                    $_SESSION['user_email'] = $user_email;
                                }
                            }

                            // Gerar iniciais para avatar
                            $initials = strtoupper(substr($user_name, 0, 1));
                            if (strpos($user_name, ' ') !== false) {
                                $name_parts = explode(' ', $user_name);
                                $last_name = end($name_parts);
                                $initials .= strtoupper(substr($last_name, 0, 1));
                            }

                            // Estatísticas de chamados
                            $chamados_stats = [
                                'abertos' => 0,
                                'em_andamento' => 0,
                                'concluidos' => 0
                            ];

                            if ($user_id > 0 && class_exists('Usuario')) {
                                $usuarioModel = new Usuario();
                                $stats = $usuarioModel->getEstatisticasChamadosUsuario($user_id);
                                if ($stats) {
                                    $chamados_stats = $stats;
                                }
                            }
                            ?>

                            <button class="user-profile-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar"><?= $initials ?></div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                                    <div class="user-role"><?= htmlspecialchars($user_role) ?></div>
                                </div>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
                                <div class="user-header">
                                    <div class="user-avatar-large"><?= $initials ?></div>
                                    <div class="user-details">
                                        <div class="user-name-large"><?= htmlspecialchars($user_name) ?></div>
                                        <div class="user-email"><?= htmlspecialchars($user_email) ?></div>
                                    </div>
                                </div>

                                <?php if (array_sum($chamados_stats) > 0): ?>
                                    <div class="user-stats">
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $chamados_stats['abertos'] ?? 0 ?></div>
                                            <div class="stat-label">Abertos</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $chamados_stats['em_andamento'] ?? 0 ?></div>
                                            <div class="stat-label">Em Andamento</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?= $chamados_stats['concluidos'] ?? 0 ?></div>
                                            <div class="stat-label">Concluídos</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>

                                <div class="user-menu">
                                    <a href="<?= base_url('perfil') ?>" class="menu-item">
                                        <i class="fas fa-user"></i>
                                        <span>Meu Perfil</span>
                                    </a>
                                    <a href="<?= base_url('chamados/meus') ?>" class="menu-item">
                                        <i class="fas fa-ticket-alt"></i>
                                        <span>Meus Chamados</span>
                                    </a>
                                    <a href="<?= base_url('ajuda') ?>" class="menu-item">
                                        <i class="fas fa-question-circle"></i>
                                        <span>Ajuda</span>
                                    </a>
                                </div>

                                <div class="dropdown-divider"></div>

                                <div class="user-footer">
                                    <a href="<?= base_url('auth/logout') ?>" class="btn-logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Sair</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <?php include ROOT_DIR . '/app/views/templates/sidebar.php'; ?>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    <!-- Breadcrumb -->
                    <?php if (isset($breadcrumbs)): ?>
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i></a></li>
                                <?php foreach ($breadcrumbs as $label => $url): ?>
                                    <?php if ($url === null): ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?= $label ?></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item"><a href="<?= $url ?>"><?= $label ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <!-- Page header -->
                    <?php if (isset($page_title)): ?>
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                            <h1 class="h3 mb-0"><?= $page_title ?></h1>
                            <?php if (isset($page_actions)): ?>
                                <div class="d-flex gap-2">
                                    <?php foreach ($page_actions as $action): ?>
                                        <a href="<?= $action['url'] ?>" class="btn <?= $action['class'] ?? 'btn-primary' ?>">
                                            <?php if (isset($action['icon'])): ?>
                                                <i class="<?= $action['icon'] ?> me-1"></i>
                                            <?php endif; ?>
                                            <?= $action['label'] ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Flash messages -->
                    <?php $flash = get_flash_message(); ?>
                    <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                            <?php
                            $icon_class = 'fa-info-circle';
                            if ($flash['type'] == 'success') $icon_class = 'fa-check-circle';
                            elseif ($flash['type'] == 'error' || $flash['type'] == 'danger') $icon_class = 'fa-exclamation-circle';
                            elseif ($flash['type'] == 'warning') $icon_class = 'fa-exclamation-triangle';
                            ?>
                            <i class="fas <?= $icon_class ?> me-2"></i>
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="container">
                        <!-- Flash messages -->
                        <?php $flash = get_flash_message(); ?>
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                                <?php
                                $icon_class = 'fa-info-circle';
                                if ($flash['type'] == 'success') $icon_class = 'fa-check-circle';
                                elseif ($flash['type'] == 'error' || $flash['type'] == 'danger') $icon_class = 'fa-exclamation-circle';
                                elseif ($flash['type'] == 'warning') $icon_class = 'fa-exclamation-triangle';
                                ?>
                                <i class="fas <?= $icon_class ?> me-2"></i>
                                <?= $flash['message'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>