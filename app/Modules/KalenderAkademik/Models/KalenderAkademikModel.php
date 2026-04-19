<?php

namespace Modules\KalenderAkademik\Models;

use CodeIgniter\Model;
use App\Models\ActivityLogModel;

class KalenderAkademikModel extends Model
{
    protected $table            = 'tbx_kalender_akademik';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tahun_ajaran_id', 'semester_id', 'kegiatan', 'tgl_mulai', 'tgl_selesai', 'warna_bg','is_deleted'];
    protected $useTimestamps    = true;

    // --- Auto Logging Events ---
    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    protected function logInsert(array $data)
    {
        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di tbx_kalender_akademik', 'ti ti-plus', 'success');
    }

    protected function logUpdate(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('UPDATE', 'Mengubah data tbx_kalender_akademik ID: ' . $id, 'ti ti-edit', 'warning');
    }

    protected function logDelete(array $data)
    {
        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        ActivityLogModel::saveLog('DELETE', 'Menghapus data tbx_kalender_akademik ID: ' . $id, 'ti ti-trash', 'danger');
    }
}