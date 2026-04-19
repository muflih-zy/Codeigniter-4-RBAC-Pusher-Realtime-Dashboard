<?= $this->extend('Modules\Layout\Views\main') ?>

<?= $this->section('content') ?>
<style>
    /* CSS agar Select2 menyatu dengan Tabler dan tidak double arrow */
    .select2-container--default .select2-selection--single {
        border: 1px solid #dadcde !important;
        height: 36px !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
    }
    /* Sembunyikan select asli agar tidak bentrok saat loading */
    .select2-dynamic { display: none; }
</style>

<div class="container-xl">
    <div id="notification-area"></div>

    <div class="card card-md border-0 shadow-sm">
        <div class="card-header">
            <div>
                <h3 class="card-title">Generator</h3>
                <p class="card-subtitle text-muted">Generate CRUD Otomatis</p>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Tabel Database</label>
                <select id="select-table" class="form-select select2-static">
                    <option value="">-- Pilih Tabel --</option>
                    <?php foreach($tables as $t): ?> 
                        <option value="<?= $t ?>"><?= $t ?></option> 
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="column-area" style="display:none;" class="mt-4">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Kolom</th>
                                <th>Label Form</th>
                                <th style="width: 280px;">Tipe Input</th>
                                <th class="text-center">Di Tabel</th>
                                <th class="text-center">Di Form</th>
                                <th class="text-center">Wajib Isi</th>
                            </tr>
                        </thead>
                        <tbody id="column-list"></tbody>
                    </table>
                </div>
                <div class="card-footer text-end mt-3 bg-transparent p-0">
                    <button type="button" class="btn btn-primary btn-pill" id="btn-generate">
                        <i class="ti ti-settings-automation icon me-2"></i> Generate CRUD Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // 1. Inisialisasi Select2 Statis (Pilih Tabel)
    $('.select2-static').select2({
        width: '100%',
        placeholder: "Pilih Tabel..."
    });

    let dbTables = <?= json_encode($tables) ?>;
    let refGroups = [];

    // Ambil data kelompok referensi
    $.get('<?= base_url('generator/getRefGroups') ?>', function(res) {
        refGroups = res;
    });

    function showAlert(message, type = 'success') {
        const icon = type === 'success' ? 'ti-check' : 'ti-alert-triangle';
        let html = `
            <div class="alert alert-important alert-${type} alert-dismissible shadow-sm mb-3" role="alert">
                <div class="d-flex">
                    <div><i class="ti ${icon} icon alert-icon me-2"></i></div>
                    <div>${message}</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert"></a>
            </div>`;
        $('#notification-area').hide().html(html).fadeIn();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // 2. Handle Ganti Tabel
    $('#select-table').on('change', function() {
        const table = $(this).val();
        if (!table) return $('#column-area').fadeOut();

        $('#column-list').html('<tr><td colspan="5" class="text-center p-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Menarik skema tabel...</td></tr>');
        $('#column-area').fadeIn();

        $.get('<?= base_url('generator/getColumns') ?>/' + table, function(data) {
            let html = '';
            data.forEach(col => {
                if (['id', 'created_at', 'updated_at', 'deleted_at', 'is_deleted'].includes(col.name)) return;

                let optTables = '<option value="">-- Pilih Tabel Ref --</option>';
                dbTables.forEach(t => { optTables += `<option value="${t}">${t}</option>`; });

                let optGroups = '<option value="">-- Pilih Kelompok Ref --</option>';
                refGroups.forEach(g => { optGroups += `<option value="${g}">${g}</option>`; });

                html += `
                <tr class="row-field">
                    <td><span class="badge bg-blue-lt">${col.name}</span></td>
                    <td>
                        <input type="text" class="form-control form-control-sm col-label" 
                               data-name="${col.name}" 
                               value="${col.name.toUpperCase().replace(/_/g, ' ')}">
                    </td>
                    <td>
                        <select class="form-select form-select-sm col-type mb-1 select2-dynamic">
                            <option value="text">Text</option>
                            <option value="number">Angka</option>
                            <option value="date">Tanggal</option>
                            <option value="date_long">Tanggal Panjang</option>
                            <option value="currency">Mata Uang</option>
                            <option value="image">Gambar</option>
                            <option value="file">File</option>
                            <option value="time">Waktu</option>
                            <option value="text_area">Textarea</option>
                            <option value="select_db">Pilihan (Tabel DB)</option>
                            <option value="select_ref">Pilihan (Tabel Referensi)</option>
                            <option value="address">address API</option>
                        </select>
                        
                        <div class="div-ref-table" style="display:none;">
                            <select class="form-select form-select-sm col-ref-table select2-dynamic">
                                ${optTables}
                            </select>
                        </div>
                        
                        <div class="div-ref-group" style="display:none;">
                            <select class="form-select form-select-sm col-ref-group select2-dynamic">
                                ${optGroups}
                            </select>
                        </div>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input col-show-table" checked>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input col-show-form" checked>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input col-required" checked>
                    </td>
                </tr>`;
            });

            $('#column-list').hide().html(html).fadeIn(function() {
                // Inisialisasi Select2 setelah elemen muncul di DOM
                $('.select2-dynamic').select2({
                    width: '100%'
                });
            });
        });
    });

    // 3. Handle Perubahan Tipe Input (Show/Hide Referensi)
    $(document).on('change', '.col-type', function() {
        const val = $(this).val();
        const parent = $(this).closest('td');

        // Sembunyikan semua div pembungkus referensi
        parent.find('.div-ref-table, .div-ref-group').hide();

        if (val === 'select_db') {
            parent.find('.div-ref-table').fadeIn();
        } else if (val === 'select_ref') {
            parent.find('.div-ref-group').fadeIn();
        }
    });

    // 4. Handle Generate
    $('#btn-generate').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        let fields = [];

        $('.row-field').each(function() {
            const row = $(this);
            fields.push({ 
                name: row.find('.col-label').data('name'), 
                label: row.find('.col-label').val(), 
                type: row.find('.col-type').val(),
                ref_table: row.find('.col-ref-table').val(),
                ref_group: row.find('.col-ref-group').val(),
                show_table: row.find('.col-show-table').is(':checked') ? 'true' : 'false',
                show_form: row.find('.col-show-form').is(':checked') ? 'true' : 'false',
                required: row.find('.col-required').is(':checked') ? 'true' : 'false'
            });
        });

        if (fields.length === 0) return;
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Membangun...');

        $.ajax({
            url: '<?= base_url('generator/generateFiles') ?>',
            type: 'POST',
            data: {
                table: $('#select-table').val(), 
                fields: fields, 
                "<?= csrf_token() ?>": "<?= csrf_hash() ?>" 
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if(res.status === 'success') {
                    showAlert(res.message, 'success');
                } else {
                    showAlert(res.message || 'Gagal', 'danger');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                showAlert('Gagal terhubung ke server', 'danger');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>