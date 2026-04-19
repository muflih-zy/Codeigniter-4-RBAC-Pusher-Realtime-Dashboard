<?php

namespace Modules\Roles\Controllers;

use App\Controllers\BaseController;
use Modules\Roles\Models\RolesModel;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class Roles extends BaseController
{
    protected $model;
    protected $moduleUrl = 'roles';

    public function __construct() {
        $this->model = new RolesModel();
    }

    public function index() {
        $access = $this->getAccess();
        if ($access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');

        return view('Modules\Roles\Views\index', [
            'title'  => 'Kelola Roles',
            'url'    => $this->moduleUrl,
            'access' => $access
        ]);
    }

    public function ajaxData() {
        $access  = $this->getAccess();
        $db = \Config\Database::connect();
        $builder = $db->table('auth_roles');
        $builder->select("auth_roles.id, auth_roles.role_name, auth_roles.description, auth_roles.layout");
        $builder->where('auth_roles.is_deleted','N');

        $dt = DataTable::of($builder)->addNumbering('no');

        $dt->edit('layout', function($row) {
            return get_ref('WIDGETS', $row->layout);
        });

        $dt->edit('is_active', function($row) {
            return get_ref('STATUS', $row->is_active);
        });

        return $dt->add('action', function($row) use ($access) {
            $btnEdit = ($access['can_update'] == 1)  
                ? '<button class="btn btn-sm btn-warning" title="Hak Akses" onclick="manageAccess(' . $row->id . ')">
            <i class="ti ti-lock"></i> Akses</button>
            <button class="btn btn-sm btn-primary" onclick="editData(' . $row->id . ')"><i class="ti ti-edit"></i></button>'
                : '<button class="btn btn-sm btn-secondary disabled"><i class="ti ti-lock"></i></button>';

            $btnDel  = ($access['can_delete'] == 1)  
                ? '<button class="btn btn-sm btn-danger" onclick="deleteData(' . $row->id . ')"><i class="ti ti-trash"></i></button>'
                : '<button class="btn btn-sm btn-secondary disabled"><i class="ti ti-lock"></i></button>';

            return '<div class="btn-list flex-nowrap">' . $btnEdit . $btnDel . '</div>';
        })->toJson(true);
    }
    public function create() {
        if ($this->getAccess()['can_create'] != 1) return 'Akses dilarang';
        return view('Modules\Roles\Views\form', ['url' => $this->moduleUrl]); 
    }

    public function edit($id) {
        if ($this->getAccess()['can_update'] != 1) return 'Akses dilarang';
        $data = $this->model->find($id);
        return view('Modules\Roles\Views\form', ['row' => $data, 'url' => $this->moduleUrl]);
    }

    public function store() {
        if ($this->getAccess()['can_create'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->handleUploads($this->request->getPost());
        if ($this->model->save($data)) {
            $this->notify('auth_roles');
            ActivityLogModel::saveLog('INSERT', "Menambah Roles", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function update($id) {
        if ($this->getAccess()['can_update'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->handleUploads($this->request->getPost(), true);
        if ($this->model->update($id, $data)) {
            $this->notify('auth_roles');
            ActivityLogModel::saveLog('UPDATE', "Update Roles ID: $id", 'ti ti-edit', 'info');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data diperbarui']);
        }
    }

    public function delete($id) {
        if ($this->getAccess()['can_delete'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        // Cek apakah data ada
        $row = $this->model->find($id);
        if (!$row) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        // Update status is_deleted menjadi Y alih-alih menghapus fisik
        // File di folder uploads tetap dipertahankan untuk arsip data
        if ($this->model->update($id, ['is_deleted' => 'Y'])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Berhasil Dihapus']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
    }

    private function handleUploads($data, $isUpdate = false)
    {
        $files = $this->request->getFiles();
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $sub = 'umum';
                    if (isset($data['nisn'])) {
                        $sub = 'siswa/' . $data['nisn'];
                    } elseif (strpos('auth_roles', 'wb_') !== false) {
                        $sub = str_replace('wb_', '', 'auth_roles');
                    }

                    $targetDir = FCPATH . 'uploads/' . $sub;
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $newName = $file->getRandomName();
                    $file->move($targetDir, $newName);
                    $data[$key] = $sub . '/' . $newName;

                    if ($isUpdate && $this->request->getPost('old_' . $key)) {
                        $oldFile = FCPATH . 'uploads/' . $this->request->getPost('old_' . $key);
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }
                } elseif ($isUpdate) {
                    $data[$key] = $this->request->getPost('old_' . $key);
                }
            }
        }
        return $data;
    }

    public function manage_access($roleId)
    {
        $db = \Config\Database::connect();
        
        $role = $db->table('auth_roles')->where('id', $roleId)->get()->getRowArray();
        
        if (!$role) {
            return "Data Role tidak ditemukan.";
        }

        // Ambil semua daftar menu
        $menus = $db->table('auth_menus')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();

        // Gunakan fungsi dari BaseController (getRolePermissions) agar data detail CRUD terbawa
        $activePermissions = $this->getRolePermissions($roleId);

        return view('Modules\Roles\Views\manage_access', [
            'role'               => $role,
            'menus'              => $menus,
            'active_permissions' => $activePermissions, // Kirim data detail
            'active_ids'         => array_keys($activePermissions) // Untuk helper in_array jika dibutuhkan
        ]);
    }

    public function save_access()
    {
        $roleId  = $this->request->getPost('role_id');
        $menuIds = $this->request->getPost('menu_id'); // Ambil array hidden input menu_id
        
        // Ambil data checkbox CRUD
        $canRead   = $this->request->getPost('can_read') ?? [];
        $canCreate = $this->request->getPost('can_create') ?? [];
        $canUpdate = $this->request->getPost('can_update') ?? [];
        $canDelete = $this->request->getPost('can_delete') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Bersihkan akses lama berdasarkan role_id
        $db->table('auth_permissions')->where('role_id', $roleId)->delete();

        // 2. Insert akses baru dengan detail CRUD
        if (!empty($menuIds)) {
            $batch = [];
            foreach ($menuIds as $mId) {
                // Hanya insert jika minimal 'can_read' dicentang
                if (isset($canRead[$mId])) {
                    $batch[] = [
                        'role_id'    => $roleId,
                        'menu_id'    => $mId,
                        'can_read'   => 1,
                        'can_create' => isset($canCreate[$mId]) ? 1 : 0,
                        'can_update' => isset($canUpdate[$mId]) ? 1 : 0,
                        'can_delete' => isset($canDelete[$mId]) ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
            }            
            if (!empty($batch)) {
                $db->table('auth_permissions')->insertBatch($batch);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui hak akses']);
        }

        $this->notify('auth_roles');
            ActivityLogModel::saveLog('INSERT', "Menambah Roles", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Hak akses berhasil diperbarui! Sidebar akan memuat ulang.']);
    }
}
