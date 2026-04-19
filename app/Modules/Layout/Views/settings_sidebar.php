<form class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSettings" aria-modal="true" role="dialog">
  <div class="offcanvas-header">
    <h2 class="offcanvas-title">Pengaturan Tampilan</h2>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div>
      <div class="mb-4">
        <label class="form-label">Mode Warna</label>
        <div class="form-selectgroup">
          <label class="form-selectgroup-item">
            <input type="radio" name="theme-mode" value="light" class="form-selectgroup-input" checked>
            <span class="form-selectgroup-label">Terang</span>
          </label>
          <label class="form-selectgroup-item">
            <input type="radio" name="theme-mode" value="dark" class="form-selectgroup-input">
            <span class="form-selectgroup-label">Gelap</span>
          </label>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Warna Utama</label>
        <div class="row g-2">
          <?php 
          $colors = ['blue', 'azure', 'indigo', 'purple', 'pink', 'red', 'orange', 'yellow', 'lime', 'green', 'teal', 'cyan'];
          foreach($colors as $c): ?>
          <div class="col-auto">
            <label class="form-colorinput">
              <input name="theme-primary" type="radio" value="<?= $c ?>" class="form-colorinput-input" <?= $c == 'blue' ? 'checked' : '' ?>>
              <span class="form-colorinput-color bg-<?= $c ?>"></span>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Status Menu</label>
        <label class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="hide-sidebar-toggle">
          <span class="form-check-label">Sembunyikan Sidebar</span>
        </label>
      </div>
    </div>
    
    <div class="mt-auto">
      <button type="button" class="btn btn-primary w-100" data-bs-dismiss="offcanvas">Terapkan</button>
    </div>
  </div>
</form>

