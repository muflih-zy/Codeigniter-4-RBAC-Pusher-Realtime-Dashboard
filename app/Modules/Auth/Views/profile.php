<?= $this->extend('Modules\Layout\Views\main') ?>

<?= $this->section('content') ?>
<div class="container-xl">
        <div id="notification-area"></div>
        <div class="card">
            <div class="row g-0">
                    <form id="form-akun" action="<?= base_url('my-account/update') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <h2 class="mb-4">My Account</h2>
                            <h3 class="card-title">Profile Details</h3>
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-xl" id="avatar-preview" style="background-image: url(<?= base_url('static/avatars/000m.jpg') ?>)"></span>
                                </div>
                                <div class="col-auto">
                                    <input type="file" name="avatar" id="input-avatar" class="d-none" accept="image/*" onchange="previewAvatar(this)">
                                    <button type="button" class="btn btn-primary" onclick="$('#input-avatar').click()">Change avatar</button>
                                </div>
                            </div>

                            <h3 class="card-title mt-4">Informasi Bisnis</h3>
                            <div class="row g-3">
                                <div class="col-md">
                                    <label class="form-label">Nama Pengguna</label>
                                    <input type="text" name="nama_user" class="form-control" value="<?= session()->get('realName') ?>">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?= session()->get('userName') ?>" readonly>
                                </div>
                            </div>

                            <h3 class="card-title mt-4">Email</h3>
                            <p class="card-subtitle">Email ini akan digunakan untuk notifikasi sistem.</p>
                            <div class="row g-2">
                                <div class="col-auto">
                                    <input type="email" name="email" class="form-control w-auto" value="admin@example.com">
                                </div>
                            </div>

                            <h3 class="card-title mt-4">Keamanan</h3>
                            <p class="card-subtitle">Anda dapat mengatur ulang kata sandi secara berkala.</p>
                            <div>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-password">
                                    Set new password
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent mt-auto">
                            <div class="btn-list justify-content-end">
                                <button type="submit" id="btn-save-akun" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-password" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-password" action="<?= base_url('my-account/change-password') ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="old_pass" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_pass" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary ms-auto">Update Password</button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#avatar-preview').css('background-image', 'url(' + e.target.result + ')');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function() {
    // AJAX untuk Update Profil
    $('#form-akun').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-save-akun');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                showAlert(res.message, res.status === 'success' ? 'success' : 'danger');
            },
            complete: function() {
                btn.prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

    // AJAX untuk Ubah Password
    $('#form-password').on('submit', function(e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function(res) {
            if(res.status === 'success') {
                $('#modal-password').modal('hide');
                showAlert(res.message, 'success');
            } else {
                alert(res.message); // Notif sederhana di dalam modal
            }
        }, 'json');
    });
});
</script>
<?= $this->endSection() ?>
