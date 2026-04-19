<?php

namespace Modules\Referensi\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class ReferensiModel extends Model
{
    protected $table            = 'auth_referensi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['RfGroup', 'Rfid', 'RfName', 'notes','is_deleted'];
    protected $useTimestamps    = true;

    // --- Auto Logging Events ---
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di auth_referensi', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data auth_referensi ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data auth_referensi ID: ' . $id, 'ti ti-trash', 'danger');
    }
}