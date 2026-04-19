<?php

namespace Modules\Pusher\Controllers;

use App\Controllers\BaseController;
use Modules\Pusher\Models\PusherModel;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class Pusher extends BaseController
{
    protected $model;
    protected $moduleUrl = 'pusher';

    public function __construct() {
        $this->model = new PusherModel();
    }

    public function index() {
        $access = $this->getAccess();
        if ($access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');

        return view('Modules\Pusher\Views\index', [
            'title'  => 'Kelola Pusher',
            'url'    => $this->moduleUrl,
            'access' => $access
        ]);
    }

    public function ajaxData() {
        $access  = $this->getAccess();
        $db = \Config\Database::connect();
        $builder = $db->table('auth_pusher');
        $builder->select("auth_pusher.id, auth_pusher.groups, auth_pusher.v_key, auth_pusher.v_value, auth_pusher.description");

        $dt = DataTable::of($builder)->addNumbering('no');

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
        return view('Modules\Pusher\Views\form', ['url' => $this->moduleUrl]); 
    }

    public function edit($id) {
        if ($this->getAccess()['can_update'] != 1) return 'Akses dilarang';
        $data = $this->model->find($id);
        return view('Modules\Pusher\Views\form', ['row' => $data, 'url' => $this->moduleUrl]);
    }

    public function store() {
        if ($this->getAccess()['can_create'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->handleUploads($this->request->getPost());
        if ($this->model->save($data)) {
            $this->notify('auth_pusher');
            ActivityLogModel::saveLog('INSERT', "Menambah Pusher", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function update($id) {
        if ($this->getAccess()['can_update'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->handleUploads($this->request->getPost(), true);
        if ($this->model->update($id, $data)) {
            $this->notify('auth_pusher');
            ActivityLogModel::saveLog('UPDATE', "Update Pusher ID: $id", 'ti ti-edit', 'info');
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
                    } elseif (strpos('auth_pusher', 'wb_') !== false) {
                        $sub = str_replace('wb_', '', 'auth_pusher');
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
