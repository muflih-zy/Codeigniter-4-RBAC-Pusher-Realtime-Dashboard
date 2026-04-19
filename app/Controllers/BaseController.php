<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ActivityLogModel;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['form', 'url', 'menu', 'text', 'setting', 'auth','pusher_helper','reference_helper','menu_helper'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload Session untuk Notifikasi
        $this->session = \Config\Services::session();
    }

    /**
     * Helper Notifikasi SweetAlert2
     * Cara pakai di Child Controller: return $this->alert('success', 'Data Berhasil Disimpan');
     */
    protected function alert($type, $message)
    {
        $this->session->setFlashdata('swal_type', $type);
        $this->session->setFlashdata('swal_msg', $message);
        return redirect()->back();
    }

    /**
     * Fungsi untuk mempermudah Response JSON (AJAX)
     */
    protected function resJson($data, $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
    protected function saveLog($action, $message)
    {
        $logModel = new \App\Models\ActivityLogModel();
        
    // Default values
        $icon = 'info-circle';
        $color = 'secondary';

    // Logika Penentuan Icon & Warna Otomatis berdasarkan Kata Kunci
        $actionLower = strtolower($action);
        
        if (strpos($actionLower, 'tambah') !== false || strpos($actionLower, 'create') !== false) {
            $icon = 'plus';
            $color = 'green';
        } elseif (strpos($actionLower, 'edit') !== false || strpos($actionLower, 'update') !== false) {
            $icon = 'edit';
            $color = 'yellow';
        } elseif (strpos($actionLower, 'hapus') !== false || strpos($actionLower, 'delete') !== false) {
            $icon = 'trash';
            $color = 'red';
        } elseif (strpos($actionLower, 'login') !== false || strpos($actionLower, 'security') !== false) {
            $icon = 'lock';
            $color = 'azure';
        }

        $data = [
            'user_id'    => auth()->id(),
            'action'     => $action,
            'message'    => $message,
            'icon'       => $icon,
            'color'      => $color,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $logModel->insert($data);
    }
    // Di dalam class BaseController extends Controller
    protected function getAccess()
    {
        $db = \Config\Database::connect();
        $roleId = session()->get('role_id'); 

        $url = $this->moduleUrl ?? '';

        return $db->table('auth_permissions a')
        ->select('a.can_create, a.can_read, a.can_update, a.can_delete')
        ->join('auth_menus b', 'b.id = a.menu_id')
        ->where('a.role_id', $roleId)
        ->where('LOWER(b.url)', strtolower($url)) 
        ->get()->getRowArray() ?? [
            'can_create' => 0,
            'can_read' => 0,
            'can_update' => 0,
            'can_delete' => 0
        ];
    }

    protected function getRolePermissions($roleId)
    {
        $db = \Config\Database::connect();
        $permissions = $db->table('auth_permissions')
                          ->where('role_id', $roleId)
                          ->get()->getResultArray();

        // Re-format agar key array adalah menu_id
        $result = [];
        foreach ($permissions as $p) {
            $result[$p['menu_id']] = $p;
        }
        return $result;
    }

    protected function notify($table)
    {
        helper('pusher');
        return push_socket($table);
    }
}