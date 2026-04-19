<?php
use Pusher\Pusher;

if (!function_exists('push_socket')) {
    function push_socket($table_name) {
        $db = \Config\Database::connect();
        
        // Ambil config dalam 1 query
        $settings = $db->table('auth_pusher')
                       ->whereIn('v_key', ['app_id', 'key', 'secret', 'cluster'])
                       ->get()->getResultArray();
                       
        $s = array_column($settings, 'v_value', 'v_key');

        if (!isset($s['key'], $s['secret'], $s['app_id'])) return false;

        $options = [
            'cluster' => $s['cluster'] ?? 'ap1',
            'useTLS'  => true
        ];

        $pusher = new Pusher($s['key'], $s['secret'], $s['app_id'], $options);

        // Kirim data ke siadu-channel
        return $pusher->trigger('siadu-channel', 'updated', [
            'table' => $table_name,
            'user'  => session()->get('username') ?? 'System'
        ]);
    }
}