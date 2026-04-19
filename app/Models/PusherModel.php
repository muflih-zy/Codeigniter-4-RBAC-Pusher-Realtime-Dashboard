<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class PusherModel extends Model
{
    protected $table            = 'pusher';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['groups', 'v_key', 'v_value', 'description'];
    protected $useTimestamps    = true;

    // Auto Logging Events
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di pusher', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data pusher ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data pusher ID: ' . $id, 'ti ti-trash', 'danger');
    }
}