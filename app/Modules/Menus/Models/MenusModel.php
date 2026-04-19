<?php

namespace Modules\Menus\Models;

use CodeIgniter\Model;

class MenusModel extends Model
{
    protected $table            = 'auth_menus';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['parent_id', 'title', 'url', 'icon', 'sort_order', 'is_active'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
