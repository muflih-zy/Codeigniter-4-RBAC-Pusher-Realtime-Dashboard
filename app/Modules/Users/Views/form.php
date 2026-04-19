<form id="form-gen" action="<?= isset($row) ? base_url('users/update/'.$row['id']) : base_url('users/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data Users</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="userName" class="form-control" placeholder="Masukkan Username..." value="<?= isset($row) ? $row['userName'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="realName" class="form-control" placeholder="Masukkan Nama Lengkap..." value="<?= isset($row) ? $row['realName'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Kelompok Pengguna</label>
            <select name="role_id" class="form-select">
                <option value="">-- Pilih Kelompok Pengguna --</option>
                <?= getOptionDb('auth_roles', $row['role_id'] ?? '') ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="text" name="userPassword" class="form-control" placeholder="Masukkan Password...">
        </div>
        <div class="mb-3">
            <label class="form-label">Unit Kerja</label>
            <select name="cabang" class="form-select">
                <option value="">-- Pilih Unit Kerja --</option>
                <?= getOptionDb('dt_unit_sekolah', $row['cabang'] ?? '') ?>
            </select>
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