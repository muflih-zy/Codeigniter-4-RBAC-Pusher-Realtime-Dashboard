<?= $this->extend('Modules\Layout\Views\main') ?>

<?= $this->section('content') ?>
    
    <div class="main-layout-section mb-4">
        <?= view("Modules\Dashboard\Views\layouts\\{$layout}", ['user' => $user]) ?>
    </div>

    <?php if (!empty($hybridWidgets)): ?>
        <hr>
        <h5 class="mb-3 text-muted">Fitur Tambahan Terdeteksi</h5>
        <div class="row">
            <?php foreach ($hybridWidgets as $widget): ?>
                <div class="col-md-6 mb-3">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-th-large mr-2"></i> 
                                <?= $widget['notes'] ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?= view("Modules\Dashboard\Views\layouts\\" . strtolower($widget['RfName'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?= $this->endSection() ?>