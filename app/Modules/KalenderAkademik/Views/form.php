<form id="form-gen" action="<?= isset($row) ? base_url('kalender-akademik/update/'.$row['id']) : base_url('kalender-akademik/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data KalenderAkademik</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">TAHUN AJARAN <span class="text-danger">*</span></label>
            <input type="text" name="tahun_ajaran_id" class="form-control" placeholder="Masukkan TAHUN AJARAN..." value="<?= isset($row) ? $row['tahun_ajaran_id'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">SEMESTER <span class="text-danger">*</span></label>
            <input type="text" name="semester_id" class="form-control" placeholder="Masukkan SEMESTER..." value="<?= isset($row) ? $row['semester_id'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">KEGIATAN <span class="text-danger">*</span></label>
            <input type="text" name="kegiatan" class="form-control" placeholder="Masukkan KEGIATAN..." value="<?= isset($row) ? $row['kegiatan'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">TANGGAL MULAI <span class="text-danger">*</span></label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?= isset($row) ? $row['tgl_mulai'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">TANGGAL SELESAI <span class="text-danger">*</span></label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?= isset($row) ? $row['tgl_selesai'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">WARNA KALENDER <span class="text-danger">*</span></label>
            <select name="warna_bg" class="form-select select2-modal" data-placeholder="-- Pilih Warna Kalender --" required>
                <option value=""></option>
                <?= getOption('WARNA KALENDER', $row['warna_bg'] ?? '') ?>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data</button>
    </div>
</form>