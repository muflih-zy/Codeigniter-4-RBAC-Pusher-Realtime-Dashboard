<?= $this->extend('Modules\Layout\Views\main') ?>

<?= $this->section('content') ?>
<div class='card'>
    <div class='card-header'>
        <h3 class='card-title text-uppercase'>Data Menus</h3>
        <div class='card-actions'>
            <?php if ($access['can_create'] == 1): ?>
                <button type='button' class='btn btn-primary' onclick='addData()'>
                    <i class='ti ti-plus me-2'></i> Tambah
                </button>
            <?php else: ?>
                <button type='button' class='btn btn-secondary disabled' title='Akses Terkunci'>
                    <i class='ti ti-lock me-2'></i> Tambah
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class='table-responsive p-3'>
        <table id='t-menus' class='table table-vcenter datatable table-striped w-100'>
            <thead>
                <tr>
                    <th width='5%'>NO</th>
                    <th>ID</th>
                    <th>MENU UTAMA</th>
                    <th>NAMA MENU</th>
                    <th>URL</th>
                    <th>ICON</th>
                    <th>URUTAN TAMPILAN</th>
                    <th>IS ACTIVE</th>
                    <th width='10%' class='text-center'>AKSI</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<div class='modal modal-blur fade' id='modal-form' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content' id='modal-content-area'></div>
    </div>
</div>


<div class='modal modal-blur fade' id='modal-success' tabindex='-1'>
    <div class='modal-dialog modal-sm modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-status bg-success'></div>
            <div class='modal-body text-center py-4'>
                <i class='ti ti-circle-check text-success mb-2' style='font-size: 3rem;'></i>
                <h3>Berhasil!</h3>
                <div class='text-secondary' id='success-msg'></div>
            </div>
            <div class='modal-footer'>
                <button class='btn btn-success w-100' data-bs-dismiss='modal'>Selesai</button>
            </div>
        </div>
    </div>
</div>


<div class='modal modal-blur fade' id='modal-confirm' tabindex='-1'>
    <div class='modal-dialog modal-sm modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-status bg-danger'></div>
            <div class='modal-body text-center py-4'>
                <i class='ti ti-alert-triangle text-danger mb-2' style='font-size: 3rem;'></i>
                <h3>Konfirmasi</h3>
                <div class='text-secondary'>Apakah anda yakin ingin menghapus data ini?</div>
            </div>
            <div class='modal-footer'>
                <div class='row w-100'>
                    <div class='col'><button class='btn w-100' data-bs-dismiss='modal'>Batal</button></div>
                    <div class='col'><button class='btn btn-danger w-100' id='btn-confirm-del'>Ya, Hapus</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var table, deleteId;
    $(document).ready(function() {
        table = $('#t-menus').DataTable({
            processing: true, 
            serverSide: true,
            order: [],
            ajax: { 
                url: '<?= base_url('menus/ajaxData') ?>', 
                type: 'POST', 
                data: d => { d.<?= csrf_token() ?> = '<?= csrf_hash() ?>'; } 
            },
            columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'id' },
            { data: 'parent_id' },
            { data: 'title' },
            { data: 'url' },
            { data: 'icon' },
            { data: 'sort_order' },
            { data: 'is_active' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function addData() { $.get('<?= base_url('menus/create') ?>', res => { $('#modal-content-area').html(res); $('#modal-form').modal('show'); }); }
    function editData(id) { $.get('<?= base_url('menus/edit/') ?>' + id, res => { $('#modal-content-area').html(res); $('#modal-form').modal('show'); }); }
    function deleteData(id) { deleteId = id; $('#modal-confirm').modal('show'); }

    $('#btn-confirm-del').on('click', function() { 
        $.post('<?= base_url('menus/delete/') ?>' + deleteId, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' }, res => {
            if(res.status==='success') { $('#modal-confirm').modal('hide'); $('#success-msg').text(res.message); $('#modal-success').modal('show'); table.ajax.reload(null, false); }
        }, 'json'); 
    });

    $(document).on('submit', '#form-gen', function(e) {
        e.preventDefault();
        $.ajax({ 
            url: $(this).attr('action'), 
            type: 'POST', 
            data: new FormData(this), 
            processData: false, 
            contentType: false,
            success: res => {
                if(res.status==='success') { 
                    $('#modal-form').modal('hide'); 
                    $('#success-msg').text(res.message); 
                    $('#modal-success').modal('show'); 
                    table.ajax.reload(null, false); 
                } else { alert(res.message); }
            }
        });
    });
</script>
<?= $this->endSection() ?>