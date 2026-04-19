<div class="row row-deck row-cards">
    <div class="col-sm-12 col-lg-6">
        <div class="card bg-primary-lt"> <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-sm d-flex flex-column">
                        <h3 class="h2 text-primary">Selamat Datang Kembali, <?= session()->get('realName') ?>!</h3>
                        <p class="text-secondary">Sistem Anda memiliki <span class="badge bg-blue-lt">5 pesan baru</span> dan <span class="badge bg-orange-lt">2 notifikasi</span> yang butuh perhatian.</p>
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
                        <span class="bg-green text-white avatar">
                            <i class="ti ti-user-check icon"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="subheader">Siswa Aktif</div>
                        <div class="h1 mb-0 me-2"><?= number_format(2135) ?></div>
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
                        <span class="bg-red text-white avatar">
                            <i class="ti ti-user-minus icon"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="subheader">Siswa Pindah</div>
                        <div class="h1 mb-0 me-2"><?= number_format(150) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kalender Akademik</h3>
            </div>
            <div class="card-body">
                <div id="calendar-academic" data-url="<?= base_url('kalender-akademik/getEvents') ?>"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Log Aktivitas Terakhir</h3>
                <a href="#" class="btn btn-sm btn-ghost-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0"> <div class="card-table table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-vcenter table-mobile-md card-table">
                        <tbody>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="w-1">
                                    <span class="bg-<?= $log->color ?>-lt avatar avatar-sm">
                                        <i class="ti ti-<?= $log->icon ?> icon"></i>
                                    </span>
                                </td>
                                <td class="td-truncate">
                                    <div class="text-truncate">
                                        <strong><?= $log->action ?></strong>: <?= $log->message ?>
                                    </div>
                                    <div class="text-secondary small"><?= $log->username ?></div>
                                </td>
                                <td class="text-nowrap text-secondary text-end">
                                    <i class="ti ti-clock icon-sm"></i> <?= date('H:i', strtotime($log->created_at)) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="<?= base_url('js/js_kalender.js') ?>"></script>
