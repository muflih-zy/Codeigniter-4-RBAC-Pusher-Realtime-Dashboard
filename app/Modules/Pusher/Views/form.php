<form id="form-gen" action="<?= isset($row) ? base_url('pusher/update/'.$row['id']) : base_url('pusher/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data Pusher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">GROUPS <span class="text-danger">*</span></label>
            <input type="text" name="groups" class="form-control" placeholder="Masukkan GROUPS..." value="<?= isset($row) ? $row['groups'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">V KEY <span class="text-danger">*</span></label>
            <input type="text" name="v_key" class="form-control" placeholder="Masukkan V KEY..." value="<?= isset($row) ? $row['v_key'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">V VALUE <span class="text-danger">*</span></label>
            <input type="text" name="v_value" class="form-control" placeholder="Masukkan V VALUE..." value="<?= isset($row) ? $row['v_value'] : '' ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">DESCRIPTION <span class="text-danger">*</span></label>
            <input type="text" name="description" class="form-control" placeholder="Masukkan DESCRIPTION..." value="<?= isset($row) ? $row['description'] : '' ?>" required>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data</button>
    </div>
</form>