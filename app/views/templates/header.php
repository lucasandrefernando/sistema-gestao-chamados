<!DOCTYPE html>
<html lang="pt-BR" class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestão de Chamados - Controle e acompanhamento de solicitações">
    <meta name="author" content="Lucas André Fernando">
    <title><?= APP_NAME ?> <?= isset($page_title) ? ' - ' . $page_title : '' ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('public/img/favicon.ico') ?>" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('public/css/main.css') ?>">
</head>

<body>
    <?php if (is_authenticated()): ?>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
            <div class="container-fluid p-0">
                <div class="navbar-container">
                    <!-- Seção esquerda - Nome da Empresa -->
                    <div class="navbar-left">
                        <button id="sidebar-toggle" class="btn btn-sm d-lg-none me-2" type="button">
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
                        <button id="theme-toggle" class="btn btn-sm me-2 d-none d-md-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Alternar tema">
                            <i class="fas fa-moon text-white"></i>
                        </button>

                        <!-- Notificações -->
                        <div class="dropdown me-2 d-none d-md-block">
                            <button class="btn btn-sm position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notificações">
                                <i class="fas fa-bell text-white"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                    <span class="visually-hidden">notificações não lidas</span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end custom-dropdown p-0" aria-labelledby="notificationDropdown" style="width: 300px;">
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <h6 class="mb-0 fs-sm">Notificações</h6>
                                    <a href="#" class="text-primary fs-xs">Marcar todas como lidas</a>
                                </div>
                                <div style="max-height: 300px; overflow-y: auto;">
                                    <a href="#" class="dropdown-item p-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fs-xs">Novo chamado aberto: #1234</p>
                                                <span class="text-muted fs-xs">Agora mesmo</span>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#" class="dropdown-item p-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fs-xs">Chamado #1230 foi concluído</p>
                                                <span class="text-muted fs-xs">2 horas atrás</span>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="#" class="dropdown-item p-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fs-xs">Chamado #1228 está pendente</p>
                                                <span class="text-muted fs-xs">5 horas atrás</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2 border-top">
                                    <a href="<?= base_url('notificacoes') ?>" class="btn btn-primary btn-sm w-100 fs-xs">Ver todas</a>
                                </div>
                            </div>
                        </div>

                        <!-- Ações rápidas -->
                        <div class="dropdown me-2 d-none d-md-block">
                            <button class="btn btn-sm" type="button" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ações rápidas">
                                <i class="fas fa-plus text-white"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end custom-dropdown" aria-labelledby="quickActionsDropdown">
                                <h6 class="dropdown-header fs-xs">Ações Rápidas</h6>
                                <a class="dropdown-item" href="<?= base_url('chamados/novo') ?>">
                                    <i class="fas fa-ticket-alt text-primary"></i> Novo Chamado
                                </a>
                                <a class="dropdown-item" href="<?= base_url('relatorios/gerar') ?>">
                                    <i class="fas fa-chart-bar text-success"></i> Gerar Relatório
                                </a>
                                <a class="dropdown-item" href="<?= base_url('usuarios/novo') ?>">
                                    <i class="fas fa-user-plus text-info"></i> Novo Usuário
                                </a>
                            </div>
                        </div>

                        <!-- Perfil do usuário -->
                        <div class="dropdown">
                            <?php
                            $user_name = $_SESSION['user_name'] ?? 'Usuário';
                            $user_role = $_SESSION['user_role'] ?? 'Usuário';
                            $initials = strtoupper(substr($user_name, 0, 1));
                            if (strpos($user_name, ' ') !== false) {
                                $name_parts = explode(' ', $user_name);
                                $last_name = end($name_parts);
                                $initials .= strtoupper(substr($last_name, 0, 1));
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
                                    <i class="fas fa-chevron-down ms-2" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end custom-dropdown" aria-labelledby="userDropdown">
                                <li class="p-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-2">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-sm"><?= htmlspecialchars($user_name) ?></div>
                                            <div class="text-muted fs-xs"><?= htmlspecialchars($_SESSION['user_email'] ?? 'email@exemplo.com') ?></div>
                                        </div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item" href="<?= base_url('perfil') ?>"><i class="fas fa-user"></i> Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('chamados/meus') ?>"><i class="fas fa-ticket-alt"></i> Meus Chamados</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('perfil/configuracoes') ?>"><i class="fas fa-cog"></i> Configurações</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= base_url('ajuda') ?>"><i class="fas fa-question-circle"></i> Ajuda</a></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                            </ul>
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