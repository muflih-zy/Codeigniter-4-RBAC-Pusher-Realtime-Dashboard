<div class="row row-deck row-cards">
    <div class="col-sm-12 col-lg-6">
        <div class="card bg-primary-lt shadow-sm"> 
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-sm d-flex flex-column">
                        <h3 class="h2 text-primary">Selamat Datang, <?= session()->get('realName') ?>!</h3>
                        <p class="text-secondary">Dashboard ini menampilkan ringkasan data akademik dan status mengajar Anda hari ini.</p>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <i class="ti ti-school icon-lg text-primary opacity-50" style="font-size: 80px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-green text-white avatar shadow">
                            <i class="ti ti-user-check icon"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="subheader">Siswa Diampu</div>
                        <div class="h1 mb-0 me-2"><?= number_format(150) ?></div> </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-blue text-white avatar shadow">
                            <i class="ti ti-clock icon"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="subheader">Jam Hari Ini</div>
                        <div class="h1 mb-0 me-2">4 Jam</div> </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kalender Akademik</h3>
            </div>
            <div class="card-body">
                <div id="calendar-academic" data-url="<?= base_url('kalender-akademik/getEvents') ?>"></div>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('js/js_kalender.js') ?>"></script>