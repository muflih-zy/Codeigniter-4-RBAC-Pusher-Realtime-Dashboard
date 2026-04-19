<?php

namespace Modules\ActivityLogs\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class ActivityLogsModel extends Model
{
    protected $table            = 'auth_activity_logs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'action', 'message', 'icon', 'color'];
    protected $useTimestamps    = true;

    // --- Auto Logging Events ---
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di auth_activity_logs', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data auth_activity_logs ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data auth_activity_logs ID: ' . $id, 'ti ti-trash', 'danger');
    }
}