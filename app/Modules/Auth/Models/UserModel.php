<?php

namespace Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'auth_users';
    protected $primaryKey       = 'userID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'userName', 
        'realName', 
        'userPassword', 
        'userDescription', 
        'userGroup', 
        'userStatus', 
        'cabang', 
        'lastLogin', 
        'token',
        'deleted'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function save_auth_log($identifier, $userId = null, $isSuccess = 0)
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();
        $agent = $request->getUserAgent();
        $data = [
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $agent->getAgentString(),
            'id_type'    => 'username',
            'identifier' => $identifier,
            'user_id'    => $userId,
            'date'       => date('Y-m-d H:i:s'),
            'success'    => $isSuccess
        ];
        return $db->table('auth_logins')->insert($data);
    }

}