<?php

namespace Modules\Users\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class UsersModel extends Model
{
    protected $table            = 'auth_users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['userName', 'realName', 'role_id', 'userPassword', 'userStatus', 'cabang','lastLogin','is_deleted'];
    protected $useTimestamps    = true;

    // --- Auto Logging Events ---
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di auth_users', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data auth_users ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data auth_users ID: ' . $id, 'ti ti-trash', 'danger');
    }

    public static function adduser($nip, $nama, $tgl_lahir)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('auth_users');
        $password_default = str_replace(['-', '/', ' '], '', $tgl_lahir);
        $data = [
            'userName'     => $nip,
            'RealName'     => $nama,
            'userPassword' => password_hash($password_default, PASSWORD_DEFAULT),
            'role_id'      => 2,
            'layout'       => 2,
            'is_deleted'   => 'N',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        return $builder->insert($data);
    }
}