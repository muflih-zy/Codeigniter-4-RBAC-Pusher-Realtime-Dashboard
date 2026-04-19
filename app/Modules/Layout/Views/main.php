    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
        <title><?= $title ?? 'Panel' ?> - SIADU</title>
        
        <link href="<?= base_url('assets/tabler/dist/css/tabler.min.css') ?>" rel="stylesheet"/>
        <link href="<?= base_url('assets/tabler/dist/css/tabler-vendors.min.css') ?>" rel="stylesheet"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script src='<?= base_url('js/address-helper.js') ?>'></script>;
        <style>
            @import url('https://rsms.me/inter/inter.css');
            :root { --tblr-font-sans-serif: 'Inter Var', sans-serif; }
            .modal-backdrop { z-index: 1040 !important; }
            .modal { z-index: 1050 !important; }
            .toast-container { z-index: 1100 !important; }
        </style>
    </head>
    <body>
        <div class="page">
            <?= $this->include('Modules\Layout\Views\header') ?>
            <?= $this->include('Modules\Layout\Views\sidebar') ?>
            <?= $this->include('Modules\Layout\Views\settings_sidebar') ?>
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="container-xl">
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
                <?= $this->include('Modules\Layout\Views\footer') ?>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="mainToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i id="toast-icon" class="ti me-2"></i> 
                        <span id="toast-message"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <script src="<?= base_url('assets/tabler/dist/js/tabler.min.js') ?>"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            function notify(message, type = 'success') {
                const toastEl = document.getElementById('mainToast');
                const iconEl = document.getElementById('toast-icon');
                const msgEl = document.getElementById('toast-message');
                
                if (!toastEl || !msgEl) return;

                // Reset UI
                $(toastEl).removeClass('bg-success bg-danger bg-warning');
                $(iconEl).removeClass('ti-circle-check ti-alert-triangle');

                if (type === 'success') {
                    $(toastEl).addClass('bg-success');
                    $(iconEl).addClass('ti-circle-check');
                } else {
                    $(toastEl).addClass('bg-danger');
                    $(iconEl).addClass('ti-alert-triangle');
                }
                
                msgEl.innerText = message;

                // Panggil Bootstrap Toast (Tabler membungkus bootstrap secara global)
                if (window.bootstrap && window.bootstrap.Toast) {
                    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                    toast.show();
                } else {
                    console.warn("Bootstrap JS library not ready.");
                }
            }

            $(document).ready(function() {
                // Flashdata check dari Controller
                <?php if (session()->getFlashdata('success')) : ?>
                notify("<?= session()->getFlashdata('success') ?>", 'success');
            <?php endif; ?>
        });
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        <?php 
        $db   = \Config\Database::connect();
        $sets = $db->table('auth_pusher')->whereIn('v_key', ['key', 'cluster'])->get()->getResultArray();
        $conf = array_column($sets, 'v_value', 'v_key');
        ?>

        const pKey = "<?= $conf['key'] ?? '' ?>";
        const pCluster = "<?= $conf['cluster'] ?? 'ap1' ?>";

        if (pKey) {
            // Inisialisasi Pusher untuk SIADU-RC
            const pusher  = new Pusher(pKey, { cluster: pCluster });
            const channel = pusher.subscribe('siadu-channel');

            channel.bind('updated', function(data) {
                console.log('Realtime update detected on table: ' + data.table);
                
                // Konvensi ID tabel Anda: #t-[nama_tabel]
                const tableId = '#t-' + data.table;

                if ($.fn.DataTable.isDataTable(tableId)) {
                    // Reload Data tanpa mereset posisi scroll/halaman
                    $(tableId).DataTable().ajax.reload(null, false);
                    
                    // Beri tanda ke user (Opsional jika pakai Toastr)
                    if (typeof toastr !== 'undefined') {
                        toastr.info('Data ' + data.table + ' diperbarui oleh ' + data.user);
                    }
                }
            });
        }
        
        const Format = {
            uang: function(nominal) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(nominal);
            },

            tglShort: function(dateString) {
                if (!dateString || dateString === '0000-00-00') return "-";
                const d = new Date(dateString);
                const day = ("0" + d.getDate()).slice(-2);
                const month = ("0" + (d.getMonth() + 1)).slice(-2);
                const year = d.getFullYear();
            return `${day}-${month}-${year}`; // Hasil: 01-02-2002
        },

        tglLong: function(dateString) {
            if (!dateString) return "-";
            const options = { day: '2-digit', month: 'long', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options).replace(/\//g, '-');
        }
    };
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
<style>
    .active-bg {
        background-color: var(--tblr-primary) !important;
        color: #ffffff !important;
        border-radius: 4px;
        margin: 2px 0px;
    }
    .active-bg .nav-link-icon, 
    .active-bg .icon {
        color: #ffffff !important;
    }

    .dropdown-item.active-bg:hover, 
    .nav-item.active-bg:hover {
        filter: brightness(90%);
    }

    /* Jika ingin warna yang lebih soft (Glassmorphism style) 
       Gunakan ini jika tidak ingin teks putih: 
    .active-bg {
        background-color: rgba(var(--tblr-primary-rgb), 0.15) !important;
        color: var(--tblr-primary) !important;
    }
    */ border-radius: 0 4px 4px 0;
}
</style>