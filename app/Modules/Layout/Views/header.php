<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item d-none d-md-flex me-3">
                <a href="#offcanvasSettings" class="nav-link px-0" data-bs-toggle="offcanvas" role="button">
                    <i class="ti ti-bell icon"></i>
                    <span class="badge bg-red"></span>
                </a>
            </div>
            <div class="nav-item d-none d-md-flex me-3">
                <div class="text-secondary small"><?= date('d M Y') ?></div>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url(<?= base_url('assets/tabler/dist/img/avatars/000m.jpg') ?>)"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div><?= session()->get('realName') ?></div>
                        <div class="mt-1 small text-secondary"><?= getnRole(session()->get('role_id')) ?></div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="<?= base_url('my-account') ?>" class="dropdown-item">Profil Saya</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= base_url('logout') ?>" class="dropdown-item text-danger">Logout</a>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="navbar-menu">
            </div>
    </div>
    <div class="nav-item d-none d-md-flex me-3">
    <a href="#offcanvasSettings" class="nav-link px-0" data-bs-toggle="offcanvas" role="button">
        <i class="ti ti-settings icon"></i>
        <span class="badge bg-red"></span>
    </a>
</div>
</header>