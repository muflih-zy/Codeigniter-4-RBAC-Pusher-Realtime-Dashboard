<?php

namespace App\Controllers;

use App\Models\PusherModel;
use Hermawan\DataTables\DataTable;

class Pusher extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new PusherModel();
    }

    public function index()
    {
        return view('pusher/index', ['title' => 'Daftar Pusher']);
    }

    public function ajaxData()
    {
        $builder = $this->model->select('id, groups, v_key, v_value, description');
        return DataTable::of($builder)
            ->addNumbering('no')
            ->add('action', function($row) {
                return '<div class="btn-list flex-nowrap">
                    <button class="btn btn-sm btn-primary" onclick="editData(' . $row->id . ')"><i class="ti ti-edit"></i></button>
                </div>';
            })
            ->toJson(true);
    }
    
    public function edit($id) { return view('pusher/form', ['row' => $this->model->find($id)]); }

    public function update($id)
    {
        if ($this->model->update($id, $this->request->getPost())) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
        }
    }
}