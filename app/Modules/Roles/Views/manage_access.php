<form id="form-gen" action="<?= base_url('roles/save_access') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="role_id" value="<?= $role['id'] ?>"> 
    
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-uppercase"><i class="ti ti-lock-access me-2"></i>HAK AKSES: <?= $role['role_name'] ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    
    <div class="modal-body p-0" style="max-height: 500px; overflow-y: auto;">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead class="sticky-top bg-light">
                    <tr>
                        <th>Menu / Modul</th>
                        <th class="text-center" width="10%">Lihat</th>
                        <th class="text-center" width="10%">Tambah</th>
                        <th class="text-center" width="10%">Ubah</th>
                        <th class="text-center" width="10%">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($menus as $m): 
                        // Cari data permission yang sudah ada untuk menu ini
                        $p = array_filter($active_permissions, function($item) use ($m) {
                            return $item['menu_id'] == $m['id'];
                        });
                        $p = reset($p); // Ambil satu data hasil filter
                    ?>
                    <tr>
                        <td>
                            <div class="font-weight-medium text-uppercase"><?= $m['title'] ?></div>
                            <div class="text-muted small italic"><?= $m['url'] ?></div>
                            <input type="hidden" name="menu_id[]" value="<?= $m['id'] ?>">
                        </td>
                        <td class="text-center">
                            <input class="form-check-input" type="checkbox" name="can_read[<?= $m['id'] ?>]" value="1" <?= (isset($p['can_read']) && $p['can_read'] == 1) ? 'checked' : '' ?>>
                        </td>
                        <td class="text-center">
                            <input class="form-check-input" type="checkbox" name="can_create[<?= $m['id'] ?>]" value="1" <?= (isset($p['can_create']) && $p['can_create'] == 1) ? 'checked' : '' ?>>
                        </td>
                        <td class="text-center">
                            <input class="form-check-input" type="checkbox" name="can_update[<?= $m['id'] ?>]" value="1" <?= (isset($p['can_update']) && $p['can_update'] == 1) ? 'checked' : '' ?>>
                        </td>
                        <td class="text-center">
                            <input class="form-check-input" type="checkbox" name="can_delete[<?= $m['id'] ?>]" value="1" <?= (isset($p['can_delete']) && $p['can_delete'] == 1) ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-2"></i>Simpan Perubahan
        </button>
    </div>
</form>