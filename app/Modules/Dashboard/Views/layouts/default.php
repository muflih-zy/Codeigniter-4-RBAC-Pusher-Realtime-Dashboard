<?= $this->extend('Modules\Layout\Views\main') ?>
<?= $this->section('content') ?>

<div class="row row-deck row-cards">
    <div class="col-sm-12 col-lg-12">
        <div class="card bg-primary-lt"> <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-sm d-flex flex-column">
                        <h3 class="h2 text-primary">Selamat Datang Kembali, <?= session()->get('realName') ?>!</h3>
                        <p class="text-secondary">Dashboard Belum Diatur <span class="badge bg-blue-lt">Layout dashboard untuk grup Anda belum tersedia di sistem.</span></p>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="ti ti-school icon-lg text-primary opacity-50" style="font-size: 80px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>