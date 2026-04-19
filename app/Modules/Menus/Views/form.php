<form id="form-gen" action="<?= isset($row) ? base_url('menus/update/'.$row['id']) : base_url('menus/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal-header">
        <h5 class="modal-title"><?= isset($row) ? 'Update' : 'Tambah' ?> Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Menu Utama</label>
            <select name='parent_id' class='form-select'>
                <option value='0'>-- Menu Utama --</option>
                <?php
                $db = \Config\Database::connect(); 
                $opts = $db->table('auth_menus')
                ->where('parent_id', 0)
                ->get()
                ->getResultArray();
                foreach($opts as $o): ?>
                    <option value='<?= $o["id"] ?>' <?= (isset($row) && $row["parent_id"] == $o["id"]) ? 'selected' : '' ?>><?= array_values($o)[2] ?>

                </option>
                <?php endforeach; ?>
            </select>        
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Menu</label>
            <input type="text" name="title" class="form-control" placeholder="Masukkan Nama Menu..." value="<?= isset($row) ? $row['title'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Url</label>
            <input type="text" name="url" class="form-control" placeholder="Masukkan Url..." value="<?= isset($row) ? $row['url'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Icon</label>
            <input type="text" name="icon" class="form-control" placeholder="Masukkan Icon..." value="<?= isset($row) ? $row['icon'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Urutan Tampilan</label>
            <input type="number" name="sort_order" class="form-control" placeholder="Masukkan Urutan Tampilan..." value="<?= isset($row) ? $row['sort_order'] : '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Is Active</label>
            <select name="is_active" class="form-select">
                <option value="">-- Pilih --</option>
                <?php $db = \Config\Database::connect(); $opts = $db->table('auth_referensi')->where('RfGroup', 'Y/T')->get()->getResultArray(); ?>
                <?php foreach($opts as $o): ?>
                    <option value="<?= $o['Rfid'] ?>" <?= (isset($row) && $row['is_active'] == $o['Rfid']) ? 'selected' : '' ?>><?= $o['RfName'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Data</button>
    </div>
</form>