<?php

/**
 * Helper untuk merender menu sidebar secara dinamis, rekursif, dan terfilter hak akses.
 */

if (!function_exists('render_sidebar_menu')) {
    function render_sidebar_menu($parentId = 0)
    {
        $db = \Config\Database::connect();
        // Ambil role_id dari session user yang login
        $roleId = session()->get('role_id'); 
        
        // Query Menu: Hanya ambil yang AKTIF dan PUNYA IZIN (can_read = 1)
        $builder = $db->table('auth_menus m');
        $builder->select('m.*');
        $builder->join('auth_permissions p', 'p.menu_id = m.id');
        $builder->where('m.parent_id', $parentId);
        $builder->where('m.is_active', 1);
        $builder->where('p.role_id', $roleId);
        $builder->where('p.can_read', 1); // Hanya yang boleh dibaca
        $builder->orderBy('m.sort_order', 'ASC');
        
        $menus = $builder->get()->getResult();

        foreach ($menus as $menu) {
            // Cek apakah menu ini memiliki anak yang juga diizinkan aksesnya
            $childBuilder = $db->table('auth_menus m');
            $childBuilder->join('auth_permissions p', 'p.menu_id = m.id');
            $childBuilder->where('m.parent_id', $menu->id);
            $childBuilder->where('m.is_active', 1);
            $childBuilder->where('p.role_id', $roleId);
            $childBuilder->where('p.can_read', 1);
            
            $hasChild = $childBuilder->countAllResults() > 0;

            // Logika Active Class & Dropdown Open
            $isCurrentPath = url_is($menu->url . '*');
            $isOpen = '';
            $activeClass = '';

            if ($hasChild) {
                // Cek apakah ada salah satu anaknya yang sedang aktif
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
                <li class="nav-item dropdown ' . ($activeClass) . '">
                    <a class="nav-link dropdown-toggle ' . ($isOpen) . '" 
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
                            
                // REKURSIF: Panggil lagi untuk anak-anaknya
                render_sidebar_menu($menu->id);
                
                echo '      </div>
                        </div>
                    </div>
                </li>';
            } else {
                if ($parentId != 0) {
                    // Item di dalam Dropdown
                    $activeSub = $isCurrentPath ? 'active' : '';
                    echo '<a class="dropdown-item ' . $activeSub . '" href="' . base_url($menu->url) . '">' . $menu->title . '</a>';
                } else {
                    // Menu Utama Single
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
    }
}
/**
 * Fungsi untuk mendapatkan title parent (untuk breadcrumb atau judul halaman)
 */
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
if (!function_exists('get_parent_menus')) {
    function get_parent_menus() {
        $db = \Config\Database::connect();
        
        return $db->table('auth_menus')
                  ->where('parent_id', 0)
                  ->get()
                  ->getResult();
    }
}

}