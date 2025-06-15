<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= base_url('public/css/style.css') ?>" rel="stylesheet">
    <link rel="icon" href="<?= base_url('public/img/favicon.ico') ?>" type="image/x-icon">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar estilizada */
        .custom-navbar {
            background-color: #2c3e50;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            height: 60px;
        }

        /* Container principal com 3 seções */
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        /* Seção esquerda - Logo e Hospital */
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 250px;
        }

        .brand-logo {
            height: 38px;
            width: auto;
        }

        .hospital-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            margin: 0;
            line-height: 1.2;
        }

        /* Seção central - Nome do sistema centralizado */
        .navbar-center {
            flex-grow: 1;
            text-align: center;
        }

        .system-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin: 0;
            color: white;
            white-space: nowrap;
        }

        /* Seção direita - Perfil do usuário */
        .navbar-right {
            display: flex;
            align-items: center;
            min-width: 250px;
            justify-content: flex-end;
        }

        /* Perfil do usuário */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: background-color 0.2s;
        }

        .user-profile:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Dropdown personalizado */
        .custom-dropdown {
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 0.6rem 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .dropdown-item i {
            color: #3498db;
            width: 16px;
            text-align: center;
        }

        /* Alertas estilizados */
        .custom-alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .alert-icon {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        /* Ajustes para responsividade */
        @media (max-width: 992px) {
            .navbar-center {
                display: none;
            }

            .navbar-container {
                justify-content: space-between;
            }
        }

        @media (max-width: 768px) {
            .hospital-name {
                display: none;
            }

            .user-info {
                display: none;
            }

            .navbar-left,
            .navbar-right {
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <?php if (is_authenticated()): ?>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
            <div class="container-fluid p-0">
                <div class="navbar-container">
                    <!-- Seção esquerda - Logo e Hospital -->
                    <div class="navbar-left">
                        <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" class="brand-logo">
                        <h2 class="hospital-name">Hospital Madre Teresa</h2>
                    </div>

                    <!-- Seção central - Nome do sistema centralizado -->
                    <div class="navbar-center">
                        <h1 class="system-title">Sistema de Gestão de Chamados</h1>
                    </div>

                    <!-- Seção direita - Perfil do usuário -->
                    <div class="navbar-right">
                        <!-- Toggle para mobile -->
                        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <!-- Perfil do usuário -->
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
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
                                        <li><a class="dropdown-item" href="<?= base_url('perfil') ?>"><i class="fas fa-user"></i> Meu Perfil</a></li>
                                        <li><a class="dropdown-item" href="<?= base_url('perfil/configuracoes') ?>"><i class="fas fa-cog"></i> Configurações</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                                    </ul>
                                </li>
                            </ul>
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