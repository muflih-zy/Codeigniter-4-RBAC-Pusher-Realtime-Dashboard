<?php

namespace Modules\Users\Controllers;

use App\Controllers\BaseController;
use Modules\Users\Models\UsersModel;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class Users extends BaseController
{
    protected $model;
    protected $moduleUrl = 'users';

    public function __construct() {
        $this->model = new UsersModel();
    }

    public function index() {
        $access = $this->getAccess();
        if ($access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');

        return view('Modules\Users\Views\index', [
            'title'  => 'Kelola Users',
            'url'    => $this->moduleUrl,
            'access' => $access
        ]);
    }

    public function ajaxData() {
        $access  = $this->getAccess();
        $db = \Config\Database::connect();
        $builder = $db->table('auth_users');
        $builder->join('auth_roles as role_id_ref', 'role_id_ref.id = auth_users.role_id', 'left');
        $builder->join('dt_unit_sekolah as cabang_ref', 'cabang_ref.id = auth_users.cabang', 'left');
        $builder->select("auth_users.id, auth_users.userName, auth_users.realName, auth_users.role_id, auth_users.cabang, auth_users.lastLogin, role_id_ref.role_name as role_id_label, cabang_ref.nama_pendek as cabang_label,role_id_ref.layout as layout");
        $builder->where('auth_users.is_deleted','N');

        $dt = DataTable::of($builder)->addNumbering('no');

        $dt->edit('role_id', function($row) {
            return $row->role_id_label ?? '-';
        });

        $dt->edit('cabang', function($row) {
            return $row->cabang_label ?? '-';
        });

        $dt->edit('layout', function($row) {
            return get_ref('WIDGETS', $row->layout);
        });

        return $dt->add('action', function($row) use ($access) {
            $btnEdit = ($access['can_update'] == 1)  
                ? '<button class="btn btn-sm btn-primary" onclick="editData(' . $row->id . ')"><i class="ti ti-edit"></i></button>'
                : '<button class="btn btn-sm btn-secondary disabled"><i class="ti ti-lock"></i></button>';

            $btnDel  = ($access['can_delete'] == 1)  
                ? '<button class="btn btn-sm btn-danger" onclick="deleteData(' . $row->id . ')"><i class="ti ti-trash"></i></button>'
                : '<button class="btn btn-sm btn-secondary disabled"><i class="ti ti-lock"></i></button>';

            return '<div class="btn-list flex-nowrap">' . $btnEdit . $btnDel . '</div>';
        })->toJson(true);
    }
    public function create() {
        if ($this->getAccess()['can_create'] != 1) return 'Akses dilarang';
        return view('Modules\Users\Views\form', ['url' => $this->moduleUrl]); 
    }

    public function edit($id) {
        if ($this->getAccess()['can_update'] != 1) return 'Akses dilarang';
        $data = $this->model->find($id);
        return view('Modules\Users\Views\form', ['row' => $data, 'url' => $this->moduleUrl]);
    }

    public function store() {
        if ($this->getAccess()['can_create'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        // 1. Ambil semua data dari input POST
        $data = $this->request->getPost();
        if (isset($data['userPassword']) && !empty($data['userPassword'])) {
            $data['userPassword'] = password_hash($data['userPassword'], PASSWORD_DEFAULT);
        } else {
        // Opsional: Jika password wajib diisi saat tambah user
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password wajib diisi']);
        }
        $data = $this->handleUploads($this->request->getPost());
        if ($this->model->save($data)) {
            $this->notify('auth_users');
            ActivityLogModel::saveLog('INSERT', "Menambah Users", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function update($id) {
        if ($this->getAccess()['can_update'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
            // 1. Tangkap semua data dari post
        $data = $this->request->getPost();
        if (isset($data['userPassword']) && !empty($data['userPassword'])) {
            $data['userPassword'] = password_hash($data['userPassword'], PASSWORD_DEFAULT);
        } else {
            unset($data['userPassword']);
        }
        // $data = $this->handleUploads($this->request->getPost(), true);
        if ($this->model->update($id, $data)) {
            $this->notify('auth_users');
            ActivityLogModel::saveLog('UPDATE', "Update Users ID: $id", 'ti ti-edit', 'info');
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
                    } elseif (strpos('auth_users', 'wb_') !== false) {
                        $sub = str_replace('wb_', '', 'auth_users');
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
}
