<form id="form-gen" action="<?= isset($row) ? base_url('roles/update/'.$row['id']) : base_url('roles/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data Roles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Kelompok</label>
            <input type="text" name="role_name" class="form-control" placeholder="Masukkan Nama Kelompok..." value="<?= isset($row) ? $row['role_name'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <input type="text" name="description" class="form-control" placeholder="Masukkan Keterangan..." value="<?= isset($row) ? $row['description'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Layout</label>
            <select name="layout" class="form-select">
                <option value="">-- Pilih Layout --</option>
                <?= getOption('WIDGETS', $row['layout'] ?? '') ?>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data</button>
    </div>
</form>