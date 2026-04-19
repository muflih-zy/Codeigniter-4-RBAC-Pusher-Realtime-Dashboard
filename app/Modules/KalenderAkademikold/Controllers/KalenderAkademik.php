<?php

namespace Modules\KalenderAkademik\Controllers;

use App\Controllers\BaseController;
use Modules\KalenderAkademik\Models\KalenderAkademikModel;
use App\Models\ActivityLogModel;
use Hermawan\DataTables\DataTable;

class KalenderAkademik extends BaseController
{
    protected $model;
    protected $moduleUrl = 'kalender-akademik';

    public function __construct() {
        $this->model = new KalenderAkademikModel();
    }

    public function index() {
        $access = $this->getAccess();
        if ($access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');

        return view('Modules\KalenderAkademik\Views\index', [
            'title'  => 'Kelola KalenderAkademik',
            'url'    => $this->moduleUrl,
            'access' => $access
        ]);
    }

    public function ajaxData() {
        $access  = $this->getAccess();
        $db = \Config\Database::connect();
        $builder = $db->table('tbx_kalender_akademik');
        $builder->join('dt_tahun_ajaran as tahun_ajaran_id_ref', 'tahun_ajaran_id_ref.id = tbx_kalender_akademik.tahun_ajaran_id', 'left');
        $builder->select("tbx_kalender_akademik.id, tbx_kalender_akademik.tahun_ajaran_id, tbx_kalender_akademik.semester_id, tbx_kalender_akademik.kegiatan, tbx_kalender_akademik.tgl_mulai, tbx_kalender_akademik.tgl_selesai, tahun_ajaran_id_ref.nama as tahun_ajaran_id_label");
        $builder->where('tbx_kalender_akademik.is_deleted','N');

        $dt = DataTable::of($builder)->addNumbering('no');

        $dt->edit('tahun_ajaran_id', function($row) {
            return $row->tahun_ajaran_id_label ?? '-';
        });

        $dt->edit('semester_id', function($row) {
            return get_ref('SEMESTER', $row->semester_id);
        });

        $dt->edit('warna_bg', function($row) {
            return get_ref('WARNA KALENDER', $row->warna_bg);
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
        return view('Modules\KalenderAkademik\Views\form', ['url' => $this->moduleUrl]); 
    }

    public function edit($id) {
        if ($this->getAccess()['can_update'] != 1) return 'Akses dilarang';
        $data = $this->model->find($id);
        return view('Modules\KalenderAkademik\Views\form', ['row' => $data, 'url' => $this->moduleUrl]);
    }

    public function store() {
        if ($this->getAccess()['can_create'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->request->getPost();
        if ($this->model->save($data)) {
            $this->notify('tbx_kalender_akademik');
            ActivityLogModel::saveLog('INSERT', "Menambah KalenderAkademik", 'ti ti-plus', 'success');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function update($id) {
        if ($this->getAccess()['can_update'] != 1) return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);

        $data = $this->request->getPost();
        if ($this->model->update($id, $data)) {
            $this->notify('tbx_kalender_akademik');
            ActivityLogModel::saveLog('UPDATE', "Update KalenderAkademik ID: $id", 'ti ti-edit', 'info');
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

    public function getEvents()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tbx_kalender_akademik');
        

        $builder->join('auth_referensi as ref_warna', 'ref_warna.Rfid = tbx_kalender_akademik.warna_bg', 'left');
        $builder->where('ref_warna.RfGroup', 'WARNA KALENDER');

        $builder->select("
            tbx_kalender_akademik.id, 
            tbx_kalender_akademik.kegiatan as title, 
            tbx_kalender_akademik.tgl_mulai as start, 
            tbx_kalender_akademik.tgl_selesai as end, 
            tbx_kalender_akademik.warna_bg as class_name,
            ref_warna.notes as hex_color 
            ");
        
        $builder->where('tbx_kalender_akademik.is_deleted', 'N');        
        $events = $builder->get()->getResultArray();
        
        return $this->response->setJSON($events);
    }
}
