<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'auth_activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = ['user_id', 'action', 'message', 'icon', 'color', 'created_at'];
    protected $useTimestamps    = true;
    protected $updatedField     = ''; // Kita hanya butuh created_at

    public function getLatestLogs($limit = 5)
    {
        return $this->select('activity_logs.*, users.username')
                    ->join('users', 'users.id = activity_logs.user_id')
                    ->orderBy('activity_logs.created_at', 'DESC')
                    ->findAll($limit);
    }
    public static function saveLog($action, $message, $icon = 'ti ti-circle', $color = 'primary')
    {
        $logModel = new self();
        $logModel->insert([
            'user_id' => session()->get('role_id') ?? 0,
            'action'  => strtoupper($action),
            'message' => $message,
            'icon'    => $icon,
            'color'   => $color
        ]);
    }
}