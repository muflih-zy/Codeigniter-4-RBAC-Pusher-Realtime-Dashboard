<form id="form-gen" action="<?= isset($row) ? base_url('referensi/update/'.$row['id']) : base_url('referensi/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data Referensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">KELOMPOK REFERENSI <span class="text-danger">*</span></label>
            <input type="text" name="RfGroup" class="form-control" placeholder="Masukkan Kelompok Referensi..." value="<?= isset($row) ? $row['RfGroup'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ISI KE DB <span class="text-danger">*</span></label>
            <input type="text" name="Rfid" class="form-control" placeholder="Masukkan Isi Ke DB..." value="<?= isset($row) ? $row['Rfid'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">TAMPIL KE FORM <span class="text-danger">*</span></label>
            <input type="text" name="RfName" class="form-control" placeholder="Masukkan Tampil Ke Form..." value="<?= isset($row) ? $row['RfName'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">KETERANGAN <span class="text-danger">*</span></label>
            <input type="text" name="notes" class="form-control" placeholder="Masukkan Keterangan..." value="<?= isset($row) ? $row['notes'] : '' ?>" required>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data</button>
    </div>
</form>