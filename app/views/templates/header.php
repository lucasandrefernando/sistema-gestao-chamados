<!DOCTYPE html>
<html lang="pt-BR" class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : '' ?>">

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
    <link rel="stylesheet" href="<?= base_url('public/css/base.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/header.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/main.css') ?>">

    <!-- Definição de variáveis JavaScript -->
    <script>
        // Define a URL base para uso em scripts JavaScript
        var BASE_URL = '<?= base_url() ?>';
    </script>

</head>

<body>
    <?php if (is_authenticated()): ?>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
            <div class="container-fluid p-0">
                <div class="navbar-container">
                    <!-- Seção esquerda - Nome da Empresa -->
                    <div class="navbar-left">
                        <button id="sidebar-toggle" class="btn btn-sm me-2" type="button" aria-label="Toggle sidebar">
                            <i class="fas fa-bars text-white"></i>
                        </button>
                        <h2 class="hospital-name">
                            <?= htmlspecialchars($_SESSION['empresa_nome'] ?? 'Hospital Madre Teresa') ?>
                        </h2>
                    </div>

                    <!-- Seção central - Nome do sistema centralizado -->
                    <div class="navbar-center">
                        <h1 class="system-title">Sistema de Gestão de Chamados</h1>
                    </div>

                    <!-- Seção direita - Ações e Perfil do usuário -->
                    <div class="navbar-right">
                        <!-- Botão de tema -->
                        <button id="theme-toggle" class="btn me-2 d-none d-md-flex" title="Alternar tema">
                            <i class="fas fa-moon"></i>
                        </button>

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
                        <div class="dropdown me-2 d-none-xs d-md-block">
                            <button class="btn position-relative" type="button"
                                id="notificationDropdown" data-bs-toggle="dropdown"
                                aria-expanded="false" title="Notificações">
                                <i class="fas fa-bell"></i>
                                <?php if ($total_notificacoes > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $total_notificacoes > 99 ? '99+' : $total_notificacoes ?>
                                        <span class="visually-hidden">notificações não lidas</span>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end custom-dropdown p-0" aria-labelledby="notificationDropdown">
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <h6 class="mb-0 fs-sm">Notificações</h6>
                                    <?php if ($total_notificacoes > 0): ?>
                                        <a href="<?= base_url('notificacoes/marcar-todas-lidas') ?>" class="text-primary fs-xs">Marcar todas como lidas</a>
                                    <?php endif; ?>
                                </div>

                                <?php if (empty($notificacoes)): ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="fas fa-bell-slash fa-2x mb-3"></i>
                                        <p>Não há notificações no momento</p>
                                    </div>
                                <?php else: ?>
                                    <div class="notification-list">
                                        <?php foreach ($notificacoes as $notificacao): ?>
                                            <div class="notification-item" data-id="<?= $notificacao['id'] ?>">
                                                <div class="notification-content">
                                                    <div class="notification-icon bg-<?= $notificacao['cor'] ?>">
                                                        <i class="<?= $notificacao['icone'] ?>"></i>
                                                    </div>
                                                    <div class="notification-text">
                                                        <div class="notification-title"><?= htmlspecialchars($notificacao['titulo']) ?></div>
                                                        <div class="notification-subtitle">
                                                            <span><?= htmlspecialchars($notificacao['descricao']) ?></span>
                                                            <span class="notification-time">• <?= $notificacao['tempo'] ?></span>
                                                        </div>
                                                        <div class="notification-actions">
                                                            <?php if ($notificacao['referencia_tipo'] == 'chamado' && $notificacao['referencia_id']): ?>
                                                                <a href="<?= base_url('chamados/visualizar/' . $notificacao['referencia_id']) ?>" class="notification-btn notification-btn-primary">Ver</a>
                                                            <?php endif; ?>
                                                            <button class="notification-btn" data-action="dismiss" data-id="<?= $notificacao['id'] ?>">Ignorar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="dropdown-footer">
                                    <a href="<?= base_url('notificacoes') ?>" class="w-100 text-center text-primary fs-xs">
                                        Ver todas as notificações
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Ações rápidas -->
                        <div class="dropdown me-2 d-none-xs d-md-block">
                            <button class="btn" type="button"
                                id="quickActionsDropdown" data-bs-toggle="dropdown"
                                aria-expanded="false" title="Ações rápidas">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end custom-dropdown p-0" aria-labelledby="quickActionsDropdown">
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <h6 class="mb-0 fs-sm">Ações Rápidas</h6>
                                </div>
                                <div class="quick-actions-grid">
                                    <a href="<?= base_url('chamados/criar') ?>" class="quick-action-item">
                                        <i class="fas fa-ticket-alt quick-action-icon"></i>
                                        <span class="quick-action-text">Novo Chamado</span>
                                    </a>
                                    <a href="<?= base_url('chamados/listar') ?>" class="quick-action-item">
                                        <i class="fas fa-search quick-action-icon"></i>
                                        <span class="quick-action-text">Buscar</span>
                                    </a>
                                    <a href="<?= base_url('dashboard') ?>" class="quick-action-item">
                                        <i class="fas fa-tachometer-alt quick-action-icon"></i>
                                        <span class="quick-action-text">Dashboard</span>
                                    </a>
                                    <a href="<?= base_url('chamados/meus') ?>" class="quick-action-item">
                                        <i class="fas fa-user-tag quick-action-icon"></i>
                                        <span class="quick-action-text">Meus Chamados</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Perfil do usuário -->
                        <div class="dropdown">
                            <?php
                            // Obter informações do usuário da sessão
                            $user_id = $_SESSION['user_id'] ?? 0;
                            $user_name = $_SESSION['user_name'] ?? 'Usuário';
                            $user_role = $_SESSION['user_role'] ?? 'Usuário';
                            $user_email = $_SESSION['user_email'] ?? '';

                            // Se o e-mail não estiver na sessão, tenta buscar do banco de dados
                            if (empty($user_email) && $user_id > 0 && class_exists('Usuario')) {
                                $usuarioModel = new Usuario();
                                $usuario = $usuarioModel->findById($user_id);
                                if ($usuario && isset($usuario['email'])) {
                                    $user_email = $usuario['email'];
                                    // Atualiza a sessão para futuras referências
                                    $_SESSION['user_email'] = $user_email;
                                }
                            }

                            // Gera as iniciais do usuário para o avatar
                            $initials = strtoupper(substr($user_name, 0, 1));
                            if (strpos($user_name, ' ') !== false) {
                                $name_parts = explode(' ', $user_name);
                                $last_name = end($name_parts);
                                $initials .= strtoupper(substr($last_name, 0, 1));
                            }

                            // Obtém estatísticas de chamados do usuário
                            $chamados_stats = [
                                'abertos' => 0,
                                'em_andamento' => 0,
                                'concluidos' => 0,
                                'total' => 0
                            ];

                            if ($user_id > 0 && class_exists('Usuario')) {
                                $usuarioModel = new Usuario();
                                $stats = $usuarioModel->getEstatisticasChamadosUsuario($user_id);
                                if ($stats) {
                                    $chamados_stats = $stats;
                                }
                            }
                            ?>
                            <a class="nav-link p-0" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-profile">
                                    <div class="user-avatar">
                                        <?= $initials ?>
                                    </div>
                                    <div class="user-info">
                                        <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                                        <span class="user-role"><?= htmlspecialchars($user_role) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-down dropdown-icon ms-2"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end custom-dropdown p-0" aria-labelledby="userDropdown">
                                <!-- Cabeçalho com informações do usuário -->
                                <div class="dropdown-user-header">
                                    <div class="dropdown-user-info">
                                        <div class="dropdown-user-avatar">
                                            <?= $initials ?>
                                        </div>
                                        <div class="dropdown-user-details">
                                            <div class="dropdown-user-name"><?= htmlspecialchars($user_name) ?></div>
                                            <div class="dropdown-user-email"><?= htmlspecialchars($user_email) ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estatísticas rápidas -->
                                <?php if (!empty($chamados_stats) && array_sum($chamados_stats) > 0): ?>
                                    <div class="dropdown-user-stats">
                                        <div class="dropdown-stat-item">
                                            <div class="dropdown-stat-value"><?= $chamados_stats['abertos'] ?? 0 ?></div>
                                            <div class="dropdown-stat-label">Abertos</div>
                                        </div>
                                        <div class="dropdown-stat-item">
                                            <div class="dropdown-stat-value"><?= $chamados_stats['em_andamento'] ?? 0 ?></div>
                                            <div class="dropdown-stat-label">Em Andamento</div>
                                        </div>
                                        <div class="dropdown-stat-item">
                                            <div class="dropdown-stat-value"><?= $chamados_stats['concluidos'] ?? 0 ?></div>
                                            <div class="dropdown-stat-label">Concluídos</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Itens do menu -->
                                <div class="dropdown-menu-items">
                                    <a class="dropdown-item" href="<?= base_url('perfil') ?>">
                                        <i class="fas fa-user"></i> Meu Perfil
                                    </a>
                                    <a class="dropdown-item" href="<?= base_url('chamados/meus') ?>">
                                        <i class="fas fa-ticket-alt"></i> Meus Chamados
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?= base_url('ajuda') ?>">
                                        <i class="fas fa-question-circle"></i> Ajuda
                                    </a>
                                    <a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>">
                                        <i class="fas fa-sign-out-alt"></i> Sair
                                    </a>
                                </div>

                                <!-- Rodapé do dropdown -->
                                <div class="dropdown-footer">
                                    <a href="<?= base_url('perfil/atividade') ?>" class="dropdown-footer-link">
                                        <i class="fas fa-history me-1"></i> Atividade
                                    </a>
                                    <a href="<?= base_url('suporte') ?>" class="dropdown-footer-link">
                                        <i class="fas fa-headset me-1"></i> Suporte
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <?php include ROOT_DIR . '/app/views/templates/sidebar.php'; ?>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    <!-- Breadcrumb -->
                    <?php if (isset($breadcrumbs)): ?>
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb fs-sm">
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
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
                        <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show custom-alert" role="alert">
                            <?php
                            $icon_class = 'fa-info-circle';
                            if ($flash['type'] == 'success') $icon_class = 'fa-check-circle';
                            elseif ($flash['type'] == 'error' || $flash['type'] == 'danger') $icon_class = 'fa-exclamation-circle';
                            elseif ($flash['type'] == 'warning') $icon_class = 'fa-exclamation-triangle';
                            ?>
                            <i class="fas <?= $icon_class ?> alert-icon"></i>
                            <div><?= $flash['message'] ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="container">
                        <!-- Flash messages -->
                        <?php $flash = get_flash_message(); ?>
                        <?php if ($flash): ?>
                            <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show custom-alert" role="alert">
                                <?php
                                $icon_class = 'fa-info-circle';
                                if ($flash['type'] == 'success') $icon_class = 'fa-check-circle';
                                elseif ($flash['type'] == 'error' || $flash['type'] == 'danger') $icon_class = 'fa-exclamation-circle';
                                elseif ($flash['type'] == 'warning') $icon_class = 'fa-exclamation-triangle';
                                ?>
                                <i class="fas <?= $icon_class ?> alert-icon"></i>
                                <div><?= $flash['message'] ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>