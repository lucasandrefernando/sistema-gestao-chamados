<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'chamados') !== false ? 'active' : '' ?>" href="<?= base_url('chamados') ?>">
                    <i class="fas fa-ticket-alt me-2"></i> Chamados
                </a>
            </li>

            <?php if (is_admin()): ?>
                <li class="nav-header mt-3 mb-2 text-muted ps-3">
                    <span>ADMINISTRAÇÃO</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'usuarios') !== false ? 'active' : '' ?>" href="<?= base_url('usuarios') ?>">
                        <i class="fas fa-users me-2"></i> Usuários
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'empresas') !== false ? 'active' : '' ?>" href="<?= base_url('empresas') ?>">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'licencas') !== false ? 'active' : '' ?>" href="<?= base_url('licencas') ?>">
                        <i class="fas fa-key me-2"></i> Licenças
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'setores') !== false ? 'active' : '' ?>" href="<?= base_url('setores') ?>">
                    <i class="fas fa-sitemap me-2"></i> Setores
                </a>
            </li>
        </ul>
    </div>
</nav>