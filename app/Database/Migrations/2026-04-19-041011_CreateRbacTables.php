<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRbacTables extends Migration
{
    public function up()
{
    $this->forge->addField([
        'id' => [
            'type'           => 'INT',
            'constraint'     => 11,
            'unsigned'       => true,
            'auto_increment' => true,
        ],
        'userName' => [
            'type'       => 'VARCHAR',
            'constraint' => '50',
        ],
        'realName' => [
            'type'       => 'VARCHAR',
            'constraint' => '100',
            'null'       => true,
        ],
        'role_id' => [
            'type'       => 'INT',
            'constraint' => 11,
            'null'       => true,
        ],
        'userPassword' => [
            'type'       => 'VARCHAR',
            'constraint' => '255',
        ],
        'userStatus' => [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'null'       => true,
            'default'    => 1,
        ],
        'cabang' => [
            'type'       => 'INT',
            'constraint' => 11,
            'default'    => 0,
        ],
        'lastLogin' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'created_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'updated_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],
        'is_deleted' => [
            'type'       => 'VARCHAR',
            'constraint' => '2',
            'null'       => true,
            'default'    => 'N',
        ],
    ]);

    $this->forge->addKey('id', true);
    $this->forge->createTable('auth_users'); // Menggunakan nama tabel sesuai screenshot Anda
}

    public function down()
    {
        //
    }
}
