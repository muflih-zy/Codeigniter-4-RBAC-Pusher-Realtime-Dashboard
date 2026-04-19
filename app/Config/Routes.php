<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 1. Static Routes (Manual)
$routes->get('/', '\Modules\Auth\Controllers\Auth::index');
$routes->get('login', '\Modules\Auth\Controllers\Auth::index');
$routes->post('login/action', '\Modules\Auth\Controllers\Auth::login_action');
$routes->get('logout', '\Modules\Auth\Controllers\Auth::logout');
$routes->get('dashboard', '\Modules\Dashboard\Controllers\Dashboard::index');

$routes->get('my-account', '\Modules\Auth\Controllers\Auth::my_account');
$routes->post('my-account/update', '\Modules\Auth\Controllers\Auth::update_account');
$routes->post('my-account/change-password', '\Modules\Auth\Controllers\Auth::change_password');

// ROUTER DATA WILAYAH
$routes->group('api/wilayah', function($routes) {
    $routes->get('provinsi', 'Wilayah::provinsi');
    $routes->get('kota/(:any)', 'Wilayah::kota/$1');
    $routes->get('kecamatan/(:any)', 'Wilayah::kecamatan/$1');
    $routes->get('desa/(:any)', 'Wilayah::desa/$1');
});

$routes->group('profil-sekolah', ['namespace' => 'Modules\ProfilSekolah\Controllers'], function($routes) {
    $routes->get('/', 'ProfilSekolah::index');
    $routes->post('update', 'ProfilSekolah::update'); // Ini yang akan dipanggil oleh action form
});
// TAMBAHKAN INI AGAR GENERATOR BISA DIAKSES
$routes->group('generator', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'Generator::index');
    $routes->post('generateFiles', 'Generator::generateFiles');
    
    // TAMBAHKAN DUA BARIS INI:
    $routes->get('getColumns/(:any)', 'Generator::getColumns/$1');
    $routes->get('getRefGroups', 'Generator::getRefGroups');

});

// 2. MODULAR AUTO-ROUTE GENERATOR
$modulePath = APPPATH . 'Modules';

if (is_dir($modulePath)) {
    // Ambil semua folder di dalam app/Modules
    $modules = array_diff(scandir($modulePath), ['.', '..', 'Auth', 'Dashboard', 'Layout']);

    foreach ($modules as $module) {
        $controllerFolder = $modulePath . '/' . $module . '/Controllers';
        
        if (is_dir($controllerFolder)) {
            $files = array_diff(scandir($controllerFolder), ['.', '..']);

            foreach ($files as $file) {
                $fileInfo = pathinfo($file);
                if (isset($fileInfo['extension']) && $fileInfo['extension'] === 'php') {
                    $controllerName = $fileInfo['filename'];
                    
                    // Slug URL: Contoh "MataPelajaran" jadi "mata-pelajaran"
                    $urlSlug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $controllerName));
                    
                    // Namespace lengkap untuk Controller di dalam Module
                    $namespace = "\\Modules\\{$module}\\Controllers";

                    $routes->group($urlSlug, ['namespace' => $namespace], function($routes) use ($controllerName) {
                        // Index & Datatables
                        $routes->get('/', "{$controllerName}::index");
                        $routes->post('ajaxData', "{$controllerName}::ajaxData");
                        
                        // CRUD Standar
                        $routes->get('create', "{$controllerName}::create");
                        $routes->post('store', "{$controllerName}::store");
                        $routes->get('edit/(:any)', "{$controllerName}::edit/$1");
                        $routes->post('update/(:any)', "{$controllerName}::update/$1");
                        $routes->post('delete/(:any)', "{$controllerName}::delete/$1");

                        // Auto-detect Method (Gunakan segment agar fleksibel)
                        $routes->get('(:segment)', "{$controllerName}::$1");
                        $routes->post('(:segment)', "{$controllerName}::$1");
                        
                        // Utility
                        $routes->get('export', "{$controllerName}::export");
                        $routes->post('import', "{$controllerName}::import");

                        $routes->get('(:segment)/(:any)', "{$controllerName}::$1/$2");
                        $routes->post('(:segment)/(:any)', "{$controllerName}::$1/$2");
                    });
                }
            }
        }
    }
}