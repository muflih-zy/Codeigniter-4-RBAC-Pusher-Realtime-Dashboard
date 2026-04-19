<?php

if (!function_exists('get_user_features')) {
    function get_user_features($userID)
    {
        $db = \Config\Database::connect();
        $features = [];

        // 1. CEK DATA MENGAJAR (Status Guru)
        // $isGuru = $db->table('db_jadwal_mengajar')
        //              ->where('guru_id', $userID)
        //              ->countAllResults();
        // if ($isGuru > 0) {
        //     $features[] = 'guru_mapel';
        // }

        // // 2. CEK DATA PERWALIAN (Status Wali Kelas)
        // $isWali = $db->table('rw_wali_kelas') 
        //              ->where('guru_id', $userID) 
        //              ->countAllResults();
        // if ($isWali > 0) {
        //     $features[] = 'wali_kelas';
        // }

        // 3. FITUR LAIN (Opsional)    
        return $features;
    }
}

// Di dalam Helper atau Fungsi Global
if (!function_exists('getnRole')) {
function getnRole($role_id) {
    $db = \Config\Database::connect();
    $role = $db->table('auth_roles')
               ->where('id', $role_id)
               ->get()
               ->getRow();

    return $role ? $role->role_name : 'Guest';
}
}