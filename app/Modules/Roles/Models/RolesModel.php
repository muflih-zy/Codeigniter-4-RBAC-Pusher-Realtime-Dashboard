<?php

namespace Modules\Roles\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class RolesModel extends Model
{
    protected $table            = 'auth_roles';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['role_name', 'description', 'layout', 'is_active','is_deleted'];
    protected $useTimestamps    = true;

    // --- Auto Logging Events ---
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di auth_roles', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data auth_roles ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data auth_roles ID: ' . $id, 'ti ti-trash', 'danger');
    }
}