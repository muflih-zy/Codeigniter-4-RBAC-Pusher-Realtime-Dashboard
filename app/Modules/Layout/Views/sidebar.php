<?php $agent = service('request')->getUserAgent(); ?>

<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">

        <?php if ($agent->isMobile()): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="navbar-brand pe-0 me-auto ms-2">
                <a href="<?= base_url() ?>" class="text-white fw-bold">
                 <i class="ti ti-school me-1"></i> SIADU
             </a>
         </div>

         <div class="navbar-nav flex-row">
            <div class="nav-item">
                <a href="#" class="nav-link px-2"><i class="ti ti-bell icon"></i>
                </a>
            </div>
            <div class="nav-item">
                <span class="avatar avatar-sm bg-blue-lt fw-bold">
                    <?= strtoupper(substr(session()->get('realName'), 0, 1)) ?>
                </span>
            </div>
        </div>

    <?php else: ?>
        <div class="navbar-brand navbar-brand-autodark mt-2">
            <a href="<?= base_url() ?>" class="text-decoration-none">
                <div class="avatar bg-primary-lt mb-2">
                    <i class="ti ti-school icon-md"></i>
                </div>
                <div class="text-white fw-bold">SIADU <span class="badge bg-green-lt ms-1">v1.0</span></div>
            </a>
        </div>
    <?php endif; ?>

    <div class="collapse navbar-collapse" id="sidebar-menu">
        <ul class="navbar-nav pt-lg-3" id="sidebar-menu-list">
            <li class="nav-item <?= (uri_string() == 'generator') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('generator') ?>">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <i class="ti ti-code icon"></i>
                </span>
                <span class="nav-link-title">Generator</span>
            </a>
        </li>
            <li class="nav-item <?= (uri_string() == 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= base_url('dashboard') ?>">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <i class="ti ti-home icon"></i>
                    </span>
                    <span class="nav-link-title">Beranda</span>
                </a>
            </li>
            <li class="nav-item"><div class="nav-link disabled fw-bold text-uppercase" style="font-size: 0.65rem;">-- Menu Utama --</div></li>
            <?php 
            helper('menu'); 
            render_sidebar_menu(0); 
            ?>
        </ul>
    </div>
</div>
</aside>