<?php

namespace Modules\Dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class Dashboard extends BaseController
{
    public function index() 
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }
        $db = \Config\Database::connect();
        $features = session()->get('user_features') ?? []; 
        $roleID   = session()->get('role_id');
        $role = $db->table('auth_roles')
        ->join('auth_referensi','auth_referensi.Rfid=auth_roles.layout') 
        ->select('auth_roles.*, auth_referensi.RfName as layout')
        ->where('auth_roles.id', $roleID)
        ->where('auth_referensi.RfGroup', 'WIDGETS')
        ->get()
        ->getRow();
        $layout = (!empty($role->layout)) ? $role->layout : 'default';
        $hybridWidgets = [];
        if (!empty($features)) {
            $hybridWidgets = $db->table('auth_referensi')
            ->where('RfGroup', 'WIDGETS')
            ->whereIn('RfName', $features)
            ->get()
            ->getResultArray();
        }
        $logModel = new ActivityLogModel();
        $data = [
            'title'         => 'Beranda',
            'layout'        => $layout,
            'hybridWidgets' => $hybridWidgets,
            'user'          => session()->get('realName'),
            'total_user' => $db->table('auth_users')->countAll(),

            // DATA LOG
            'logs'       => $logModel->select('auth_activity_logs.*, auth_users.username')
            ->join('auth_users', 'auth_users.id = auth_activity_logs.user_id')
            ->orderBy('auth_activity_logs.created_at', 'DESC')
            ->findAll(5)
        ];

        return view('Modules\Dashboard\Views\index', $data);
    }

}