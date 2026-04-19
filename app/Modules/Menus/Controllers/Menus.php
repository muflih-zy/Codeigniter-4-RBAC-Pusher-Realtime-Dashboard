<?php

namespace Modules\Menus\Controllers;

use App\Controllers\BaseController;
use Modules\Menus\Models\MenusModel;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class Menus extends BaseController
{
    protected $model;
    protected $moduleUrl = 'menus';

    public function __construct() {
        $this->model = new MenusModel();
    }

    public function index() {
        $access = $this->getAccess();
        if ($access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');

        return view('Modules\Menus\Views\index', [
            'title'  => 'Kelola Menus',
            'url'    => $this->moduleUrl,
            'access' => $access
        ]);
    }

    public function ajaxData() {
        $access  = $this->getAccess();
        $builder = $this->model->select('id, parent_id, title, url, icon, sort_order, is_active');

        return DataTable::of($builder)->addNumbering('no')->add('action', function($row) use ($access) {
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
        return view('Modules\Menus\Views\form', ['url' => $this->moduleUrl]); 
    }

    public function edit($id) {
        if ($this->getAccess()['can_update'] != 1) return 'Akses dilarang';
        $data = $this->model->find($id);
        return view('Modules\Menus\Views\form', ['row' => $data, 'url' => $this->moduleUrl]);
    }

    public function store() {
        if ($this->getAccess()['can_create'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        if ($this->model->save($this->request->getPost())) {
            $postData = $this->request->getPost();
            $this->notify('auth_menus');
            $label = $postData['title'] ?? 'Menu Baru';
            ActivityLogModel::saveLog('INSERT', "Menambah Menu : $label", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }
    public function storegen() {
        if ($this->getAccess()['can_create'] != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $postData = $this->request->getPost();
        
        if ($this->model->save($postData)) {
        // Panggil helper di sini
            helper('generator'); 
            generate_module_files($postData['url'] ?? '');

            $this->notify('auth_menus');
            $label = $postData['title'] ?? 'Menu Baru';
            ActivityLogModel::saveLog('INSERT', "Menambah Menus: $label", 'ti ti-plus', 'success');
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function update($id) {
        if ($this->getAccess()['can_update'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        if ($this->model->update($id, $this->request->getPost())) {
            $this->notify('auth_menus');
            ActivityLogModel::saveLog('UPDATE', "Update Menus ID: $id", 'ti ti-edit', 'info');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data diperbarui']);
        }
    }

    public function delete($id) {
        if ($this->getAccess()['can_delete'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $row = $this->model->find($id);
        if ($row) {
            foreach ($row as $key => $val) {
                $filePath = FCPATH . 'uploads/' . $val;
                if (!empty($val) && file_exists($filePath) && !is_dir($filePath)) @unlink($filePath);
            }
        }

        if ($this->model->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Berhasil Dihapus']);
        }
    }
    private function handleUploads($data, $isUpdate = false)
    {
        $files = $this->request->getFiles();

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                if ($file->isValid() && !$file->hasMoved()) {

                    // Tentukan sub-folder upload
                    $sub = 'umum';
                    if (isset($data['nisn'])) {
                        $sub = 'siswa/' . $data['nisn'];
                    } elseif (strpos('auth_menus', 'wb_') !== false) {
                        $sub = str_replace('wb_', '', 'auth_menus');
                    }

                    $targetDir = FCPATH . 'uploads/' . $sub;
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $newName = $file->getRandomName();
                    $file->move($targetDir, $newName);
                    $data[$key] = $sub . '/' . $newName;

                    // Hapus file lama saat update
                    if ($isUpdate && $this->request->getPost('old_' . $key)) {
                        $oldFile = FCPATH . 'uploads/' . $this->request->getPost('old_' . $key);
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                        }
                    }

                } elseif ($isUpdate) {
                    $data[$key] = $this->request->getPost('old_' . $key);
                }
            }
        }

        return $data;
    }
}
