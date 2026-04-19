<?php

/**
 * Helper untuk merender menu sidebar secara dinamis, rekursif, dan terfilter hak akses.
 */

if (!function_exists('render_sidebar_menu')) {
    function render_sidebar_menu($parentId = 0, $roleOverride = null)
    {
        $db = \Config\Database::connect();
        
        // Tentukan Role ID: Gunakan override jika ada (untuk menu Guru/Wali), 
        // jika tidak gunakan dari session.
        $roleId = $roleOverride ?? session()->get('role_id');
        $userName = session()->get('userName');

        // --- Logika Deteksi Peran Tambahan (Hanya dijalankan pada level root/parent 0) ---
        $isGuru = false;
        $isWali = false;

        if ($parentId === 0 && !$roleOverride) {
            $gtk = $db->table('db_gtk')->where('nip', $userName)->get()->getRow();
            if ($gtk) {
                $id_guru = $gtk->id;
                // Cek Jadwal Mengajar
                $isGuru = $db->table('db_jadwal_mengajar')->where('guru_id', $id_guru)->countAllResults() > 0;
                // Cek Wali Kelas
                $isWali = $db->table('rw_wali_kelas')->where('guru_id', $id_guru)->countAllResults() > 0;
            }
        }

        // --- 1. Render Menu Utama / Sistem ---
        $builder = $db->table('auth_menus m');
        $builder->select('m.*');
        $builder->join('auth_permissions p', 'p.menu_id = m.id');
        $builder->where('m.parent_id', $parentId);
        $builder->where('m.is_active', 1);
        $builder->where('p.role_id', $roleId);
        $builder->where('p.can_read', 1);
        $builder->orderBy('m.sort_order', 'ASC');
        
        $menus = $builder->get()->getResult();

        foreach ($menus as $menu) {
            // Cek Child
            $childBuilder = $db->table('auth_menus m');
            $childBuilder->join('auth_permissions p', 'p.menu_id = m.id');
            $childBuilder->where('m.parent_id', $menu->id);
            $childBuilder->where('m.is_active', 1);
            $childBuilder->where('p.role_id', $roleId);
            $childBuilder->where('p.can_read', 1);
            
            $hasChild = $childBuilder->countAllResults() > 0;

            $isCurrentPath = url_is($menu->url . '*');
            $isOpen = '';
            $activeClass = '';

            if ($hasChild) {
                $checkChildren = $db->table('auth_menus m')
                    ->join('auth_permissions p', 'p.menu_id = m.id')
                    ->where('m.parent_id', $menu->id)
                    ->where('p.role_id', $roleId)
                    ->get()->getResult();

                foreach ($checkChildren as $child) {
                    if (url_is($child->url . '*')) {
                        $isOpen = 'show';
                        $activeClass = 'active';
                        break;
                    }
                }
            } elseif ($isCurrentPath) {
                $activeClass = 'active';
            }

            // RENDER HTML
            if ($hasChild) {
                echo '
                <li class="nav-item dropdown ' . $activeClass . '">
                    <a class="nav-link dropdown-toggle ' . $isOpen . '" 
                       href="#navbar-menu-' . $menu->id . '" 
                       data-bs-toggle="dropdown" 
                       data-bs-auto-close="false" 
                       role="button">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <i class="' . $menu->icon . '"></i>
                        </span>
                        <span class="nav-link-title">' . $menu->title . '</span>
                    </a>
                    <div class="dropdown-menu ' . $isOpen . '">
                        <div class="dropdown-menu-columns">
                            <div class="dropdown-menu-column">';
                            
                render_sidebar_menu($menu->id, $roleId); // Teruskan Role ID Aktif
                
                echo '      </div>
                        </div>
                    </div>
                </li>';
            } else {
                if ($parentId != 0) {
                    $activeSub = $isCurrentPath ? 'active' : '';
                    echo '<a class="dropdown-item ' . $activeSub . '" href="' . base_url($menu->url) . '">' . $menu->title . '</a>';
                } else {
                    echo '
                    <li class="nav-item ' . $activeClass . '">
                        <a class="nav-link" href="' . base_url($menu->url) . '">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="' . $menu->icon . '"></i>
                            </span>
                            <span class="nav-link-title">' . $menu->title . '</span>
                        </a>
                    </li>';
                }
            }
        }

        // --- 2. Render Otomatis Menu Tambahan (Guru/Wali) ---
        // Hanya muncul jika sedang merender root (parentId 0) dan bukan sedang dalam mode override
        if ($parentId === 0 && !$roleOverride) {
            if ($isGuru) {
                echo '<li class="nav-item mt-3"><div class="nav-link disabled fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">-- Menu Guru --</div></li>';
                render_sidebar_menu(0, 2); // Role 2 = Guru
            }
            if ($isWali) {
                echo '<li class="nav-item mt-2"><div class="nav-link disabled fw-bold text-uppercase text-muted" style="font-size: 0.65rem;">-- Menu Wali Kelas --</div></li>';
                render_sidebar_menu(0, 3); // Role 3 = Wali Kelas
            }
        }
    }
}

if (!function_exists('get_parent_menu_title')) {
    function get_parent_menu_title($current_title) {
        $db = \Config\Database::connect();
        $menu = $db->table('auth_menus')->where('title', $current_title)->get()->getRow();
        
        if ($menu && $menu->parent_id != 0) {
            $parent = $db->table('auth_menus')->where('id', $menu->parent_id)->get()->getRow();
            return $parent ? $parent->title : 'Panel';
        }
        return 'Panel';
    }
}

if (!function_exists('get_parent_menus')) {
    function get_parent_menus() {
        $db = \Config\Database::connect();
        return $db->table('auth_menus')
                  ->where('parent_id', 0)
                  ->get()
                  ->getResult();
    }
}