<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */
function has_access($url) {
    $session = session();
    $roleID = $session->get('roleID'); 
    
    // DEBUG: Hapus tanda komentar dua baris di bawah ini untuk melihat isinya
    // echo "Role Anda: " . $roleID . " | Mencoba akses: " . $url; 
    // die();

    if ($roleID == 1) return true; // Bypass Superadmin

    $db = \Config\Database::connect();
    return $db->table('auth_permissions')
              ->join('auth_menus', 'auth_menus.id = auth_permissions.menu_id')
              ->where('auth_permissions.role_id', $roleID)
              ->where('auth_menus.url', $url)
              ->where('auth_permissions.can_read', 1)
              ->countAllResults() > 0;
}