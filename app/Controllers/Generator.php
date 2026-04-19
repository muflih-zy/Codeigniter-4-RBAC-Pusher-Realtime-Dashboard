<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class Generator extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function index()
    {
        $allTables = $this->db->listTables();
        $exclude = ['migrations']; 
        $tables = array_values(array_diff($allTables, $exclude));

        $data = [
            'tables' => $tables,
            'title'  => "Visi-Gen: Modular CRUD Builder"
        ];

        return view('generator/index', $data);
    }

    public function getColumns($table)
    {
        $fields = $this->db->getFieldData($table);
        return $this->response->setJSON($fields);
    }

    public function getRefGroups()
    {
        $query = $this->db->table('auth_referensi')
        ->select('RfGroup')
        ->distinct()
        ->get();

        $groups = [];
        foreach ($query->getResult() as $row) {
            $groups[] = $row->RfGroup;
        }

        return $this->response->setJSON($groups);
    }

    private function generateSlug($text)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $text));
    }

    public function generateFiles()
    {
        $table  = $this->request->getPost('table');
        $fields = $this->request->getPost('fields');
        $pk     = $this->request->getPost('primary_key') ?? 'id';

        if (empty($table)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tabel belum dipilih!']);
        }

        // Clean Table Name (Hapus prefix jika ada, misal: tbl_users -> Users)
        $cleanName = preg_replace('/^[^_]*_/', '', $table);
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $cleanName)));
        
        $modulePath = APPPATH . "Modules/{$className}";
        $paths = [
            $modulePath . "/Controllers",
            $modulePath . "/Models",
            $modulePath . "/Views"
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        // Generate Files
        file_put_contents($modulePath . "/Models/{$className}Model.php", $this->buildModel($className, $table, $fields, $pk));
        file_put_contents($modulePath . "/Controllers/{$className}.php", $this->buildController($className, $table, $fields, $pk));
        file_put_contents($modulePath . "/Views/index.php", $this->buildIndexView($className, $table, $fields, $pk));
        file_put_contents($modulePath . "/Views/form.php", $this->buildFormView($className, $table, $fields, $pk));

        return $this->response->setJSON(['status' => 'success', 'message' => "Module {$className} Berhasil di-generate!"]);
    }

    // --------------------------------------------------------------------
    // BUILDER LOGIC (DENGAN PERBAIKAN NEWLINE \n)
    // --------------------------------------------------------------------

    private function buildModel($className, $table, $fields)
    {
        $allowed = [];
        $searchable = [];
        $joins = "";
        
    // Mulai dengan ID karena diperlukan untuk aksi (edit/delete)
        $selectFields = ["{$table}.id"]; 

        foreach ($fields as $f) {
            $name = $f['name'];
            if (empty($name) || $name === 'id') continue;

            $allowed[] = $name;

        // Logika untuk kolom yang tampil di tabel Datatables
            if (($f['show_table'] ?? 'false') === 'true') {
                if ($f['type'] === 'select_db' && !empty($f['ref_table'])) {
                    $refTable = $f['ref_table'];
                    $refAlias = "ref_" . $name;
                    
                // Tambahkan ke JOIN
                    $joins .= "        \$builder->join('{$refTable} as {$refAlias}', '{$refAlias}.id = {$table}.{$name}', 'left');\n";
                    
                // Ambil ID asli dan Label (asumsi kolom label di tabel tujuan adalah 'nama')
                    $selectFields[] = "{$table}.{$name}";
                    $selectFields[] = "{$refAlias}.nama as {$name}_label";
                    
                // Pencarian diarahkan ke nama/label, bukan ID
                    $searchable[] = "{$refAlias}.nama";
                } else {
                    $selectFields[] = "{$table}.{$name}";
                    $searchable[] = "{$table}.{$name}";
                }
            }
        }

        $allowedStr = "'" . implode("', '", $allowed) . "'";
        $selectStr = "'" . implode("', '", $selectFields) . "'";
        
    // Logic searching
        $searchLogic = "";
        if (!empty($searchable)) {
            $searchLogic .= "        if (!empty(\$post['search']['value'])) {\n";
            $searchLogic .= "            \$searchValue = \$post['search']['value'];\n";
            $searchLogic .= "            \$builder->groupStart();\n";
            foreach ($searchable as $index => $col) {
                $method = ($index === 0) ? 'like' : 'orLike';
                $searchLogic .= "            \$builder->{$method}('{$col}', \$searchValue);\n";
            }
            $searchLogic .= "            \$builder->groupEnd();\n";
            $searchLogic .= "        }\n";
        }

    // --- Template Generation ---
        $code = "<?php\n\nnamespace Modules\\{$className}\\Models;\n\n";
        $code .= "use CodeIgniter\Model;\n";
        $code .= "use App\Models\ActivityLogModel;\n\n";
        $code .= "class {$className}Model extends Model\n{\n";
        $code .= "    protected \$table            = '{$table}';\n";
        $code .= "    protected \$primaryKey       = 'id';\n";
        $code .= "    protected \$allowedFields    = [{$allowedStr},'is_deleted'];\n";
        $code .= "    protected \$useTimestamps    = true;\n\n";

        $code .= "    // --- Auto Logging Events ---\n";
        $code .= "    protected \$afterInsert = ['logInsert'];\n";
        $code .= "    protected \$afterUpdate = ['logUpdate'];\n";
        $code .= "    protected \$afterDelete = ['logDelete'];\n\n";

        $code .= "    protected function logInsert(array \$data)\n    {\n";
        $code .= "        ActivityLogModel::saveLog('INSERT', 'Menambah data baru di {$table}', 'ti ti-plus', 'success');\n";
        $code .= "    }\n\n";

        $code .= "    protected function logUpdate(array \$data)\n    {\n";
        $code .= "        \$id = is_array(\$data['id']) ? \$data['id'][0] : \$data['id'];\n";
        $code .= "        ActivityLogModel::saveLog('UPDATE', 'Mengubah data {$table} ID: ' . \$id, 'ti ti-edit', 'warning');\n";
        $code .= "    }\n\n";

        $code .= "    protected function logDelete(array \$data)\n    {\n";
        $code .= "        \$id = is_array(\$data['id']) ? \$data['id'][0] : \$data['id'];\n";
        $code .= "        ActivityLogModel::saveLog('DELETE', 'Menghapus data {$table} ID: ' . \$id, 'ti ti-trash', 'danger');\n";
        $code .= "    }\n";
        $code .= "}";

        return $code;
    }

    private function buildController($className, $table, $fields, $pk)
    {
        $lowerClass = $this->generateSlug($className);
        $tableFields = [];
        foreach ($fields as $f) {
            if ($f['show_table'] === 'true') $tableFields[] = $f['name'];
        }
        if (!in_array($pk, $tableFields)) array_unshift($tableFields, $pk);
        $selectStr = implode(', ', $tableFields);

        $code = "<?php\n\nnamespace Modules\\{$className}\\Controllers;\n\n";
        $code .= "use App\Controllers\BaseController;\n";
        $code .= "use Modules\\{$className}\\Models\\{$className}Model;\n";
        $code .= "use App\Models\ActivityLogModel;\n";
        $code .= "use Hermawan\DataTables\DataTable;\n\n";
        $code .= "class {$className} extends BaseController\n{\n";
        $code .= "    protected \$model;\n";
        $code .= "    protected \$moduleUrl = '{$lowerClass}';\n\n";
        $code .= "    public function __construct() {\n";
        $code .= "        \$this->model = new {$className}Model();\n";
        $code .= "    }\n\n";

    // INDEX
        $code .= "    public function index() {\n";
        $code .= "        \$access = \$this->getAccess();\n";
        $code .= "        if (\$access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');\n\n";
        $code .= "        return view('Modules\\{$className}\\Views\\index', [\n";
        $code .= "            'title'  => 'Kelola {$className}',\n";
        $code .= "            'url'    => \$this->moduleUrl,\n";
        $code .= "            'access' => \$access\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
// AJAX DATA
        $code .= "    public function ajaxData() {\n";
        $code .= "        \$access  = \$this->getAccess();\n";
        $code .= "        \$db = \\Config\\Database::connect();\n";
        $code .= "        \$builder = \$db->table('{$table}');\n";
        $fieldsArr = explode(',', $selectStr);
        $selectArr = [];

        foreach ($fieldsArr as $sf) {
            $sf = trim($sf);
            $selectArr[] = "{$table}.{$sf}";
        }
        foreach ($fields as $f) {
            if ($f['type'] === 'select_db') {
                $table_ref = $f['ref_table'];
                $alias = $f['name'] . '_ref';
                $labelField = $f['label_field'] ?? 'nama';
                $code .= "        \$builder->join('{$table_ref} as {$alias}', '{$alias}.id = {$table}.{$f['name']}', 'left');\n";
                $selectArr[] = "{$alias}.{$labelField} as {$f['name']}_label";
            }
        }
        $selectFinal = implode(', ', $selectArr);
        $code .= "        \$builder->select(\"{$selectFinal}\");\n";
        $code .= "        \$builder->where('{$table}.is_deleted','N');\n\n";
        $code .= "        \$dt = DataTable::of(\$builder)->addNumbering('no');\n\n";
        foreach ($fields as $f) {

            if ($f['type'] === 'select_db') {
                $code .= "        \$dt->edit('{$f['name']}', function(\$row) {\n";
                $code .= "            return \$row->{$f['name']}_label ?? '-';\n";
                $code .= "        });\n\n";
            }

            elseif ($f['type'] === 'select_ref') {
                $code .= "        \$dt->edit('{$f['name']}', function(\$row) {\n";
                $code .= "            return get_ref('{$f['ref_group']}', \$row->{$f['name']});\n";
                $code .= "        });\n\n";
            }
        }
        $code .= "        return \$dt->add('action', function(\$row) use (\$access) {\n";
        $code .= "            \$btnEdit = (\$access['can_update'] == 1)  \n";
        $code .= "                ? '<button class=\"btn btn-sm btn-primary\" onclick=\"editData(' . \$row->id . ')\"><i class=\"ti ti-edit\"></i></button>'\n";
        $code .= "                : '<button class=\"btn btn-sm btn-secondary disabled\"><i class=\"ti ti-lock\"></i></button>';\n\n";

        $code .= "            \$btnDel  = (\$access['can_delete'] == 1)  \n";
        $code .= "                ? '<button class=\"btn btn-sm btn-danger\" onclick=\"deleteData(' . \$row->id . ')\"><i class=\"ti ti-trash\"></i></button>'\n";
        $code .= "                : '<button class=\"btn btn-sm btn-secondary disabled\"><i class=\"ti ti-lock\"></i></button>';\n\n";

        $code .= "            return '<div class=\"btn-list flex-nowrap\">' . \$btnEdit . \$btnDel . '</div>';\n";
        $code .= "        })->toJson(true);\n";

        $code .= "    }\n";

        
    // CREATE
        $code .= "    public function create() {\n";
        $code .= "        if (\$this->getAccess()['can_create'] != 1) return 'Akses dilarang';\n";
        $code .= "        return view('Modules\\{$className}\\Views\\form', ['url' => \$this->moduleUrl]); \n";
        $code .= "    }\n\n";

    // EDIT
        $code .= "    public function edit(\$id) {\n";
        $code .= "        if (\$this->getAccess()['can_update'] != 1) return 'Akses dilarang';\n";
        $code .= "        \$data = \$this->model->find(\$id);\n";
        $code .= "        return view('Modules\\{$className}\\Views\\form', ['row' => \$data, 'url' => \$this->moduleUrl]);\n";
        $code .= "    }\n\n";

    // STORE
        $code .= "    public function store() {\n";
        $code .= "        if (\$this->getAccess()['can_create'] != 1) return \$this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);\n\n";
        $code .= "        \$data = \$this->handleUploads(\$this->request->getPost());\n";
        $code .= "        if (\$this->model->save(\$data)) {\n";
        $code .= "            \$this->notify('{$table}');\n";
        $code .= "            ActivityLogModel::saveLog('INSERT', \"Menambah {$className}\", 'ti ti-plus', 'success');\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";

    // UPDATE
        $code .= "    public function update(\$id) {\n";
        $code .= "        if (\$this->getAccess()['can_update'] != 1) return \$this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);\n\n";
        $code .= "        \$data = \$this->handleUploads(\$this->request->getPost(), true);\n";
        $code .= "        if (\$this->model->update(\$id, \$data)) {\n";
        $code .= "            \$this->notify('{$table}');\n";
        $code .= "            ActivityLogModel::saveLog('UPDATE', \"Update {$className} ID: \$id\", 'ti ti-edit', 'info');\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data diperbarui']);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
// DELETE (SOFT DELETE MODE)
        $code .= "    public function delete(\$id) {\n";
        $code .= "        if (\$this->getAccess()['can_delete'] != 1) return \$this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);\n\n";

        $code .= "        // Cek apakah data ada\n";
        $code .= "        \$row = \$this->model->find(\$id);\n";
        $code .= "        if (!\$row) {\n";
        $code .= "            return \$this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);\n";
        $code .= "        }\n\n";

        $code .= "        // Update status is_deleted menjadi Y alih-alih menghapus fisik\n";
        $code .= "        // File di folder uploads tetap dipertahankan untuk arsip data\n";
        $code .= "        if (\$this->model->update(\$id, ['is_deleted' => 'Y'])) {\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data Berhasil Dihapus']);\n";
        $code .= "        } else {\n";
        $code .= "            return \$this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";

    // HANDLE UPLOADS
        $code .= "    private function handleUploads(\$data, \$isUpdate = false)\n";
        $code .= "    {\n";
        $code .= "        \$files = \$this->request->getFiles();\n";
        $code .= "        if (!empty(\$files)) {\n";
        $code .= "            foreach (\$files as \$key => \$file) {\n";
        $code .= "                if (\$file->isValid() && !\$file->hasMoved()) {\n";
        $code .= "                    \$sub = 'umum';\n";
        $code .= "                    if (isset(\$data['nisn'])) {\n";
        $code .= "                        \$sub = 'siswa/' . \$data['nisn'];\n";
        $code .= "                    } elseif (strpos('{$table}', 'wb_') !== false) {\n";
        $code .= "                        \$sub = str_replace('wb_', '', '{$table}');\n";
        $code .= "                    }\n\n";
        $code .= "                    \$targetDir = FCPATH . 'uploads/' . \$sub;\n";
        $code .= "                    if (!is_dir(\$targetDir)) mkdir(\$targetDir, 0777, true);\n\n";
        $code .= "                    \$newName = \$file->getRandomName();\n";
        $code .= "                    \$file->move(\$targetDir, \$newName);\n";
        $code .= "                    \$data[\$key] = \$sub . '/' . \$newName;\n\n";
        $code .= "                    if (\$isUpdate && \$this->request->getPost('old_' . \$key)) {\n";
        $code .= "                        \$oldFile = FCPATH . 'uploads/' . \$this->request->getPost('old_' . \$key);\n";
        $code .= "                        if (file_exists(\$oldFile)) @unlink(\$oldFile);\n";
        $code .= "                    }\n";
        $code .= "                } elseif (\$isUpdate) {\n";
        $code .= "                    \$data[\$key] = \$this->request->getPost('old_' . \$key);\n";
        $code .= "                }\n";
        $code .= "            }\n";
        $code .= "        }\n";
        $code .= "        return \$data;\n";
        $code .= "    }\n";

        $code .= "}\n";
        return $code;
    }

    private function buildIndexView($className, $table, $fields)
    {
        $lowerClass = $this->generateSlug($className);
        $th = ""; 
    // Kolom No menggunakan data: 'no' dari Controller
        $jsCols = "                { data: 'no', orderable: false, searchable: false, className: 'text-center' },\n";

        foreach ($fields as $f) {
            if (($f['show_table'] ?? 'false') === 'true') {
                $label = strtoupper($f['label']);
                $th .= "                            <th>{$label}</th>\n";
                
        // Cek jika tipenya image atau file
                if (in_array($f['type'], ['image', 'file'])) {
                    $jsCols .= "                { 
                        data: '{$f['name']}', 
                        className: 'text-nowrap',
                        render: function(data, type, row) {
                            if (data && data !== '-') {
                                return `<a href=\"<?= base_url('uploads/') ?>/\${data}\" target=\"_blank\" class=\"btn btn-ghost-primary btn-sm btn-pill\"><i class=\"ti ti-file-search me-1\"></i> Preview</a>`;
                            }
                            return '<span class=\"text-muted\">-</span>';
                        }
                    },\n";
                } 
                // LOGIKA BARU: Format Uang & Tanggal di DataTable
            else if ($f['type'] === 'currency') {
                $jsCols .= "                { data: '{$f['name']}', className: 'text-nowrap', render: d => Format.uang(d) },\n";
            } else if (in_array($f['type'], ['image', 'file'])) {
                $jsCols .= "                { data: '{$f['name']}', className: 'text-nowrap', render: d => Format.tglShort(d) },\n";
            } else if ($f['type'] === 'date_long') {
                $jsCols .= "                { data: '{$f['name']}', className: 'text-nowrap', render: d => Format.tglLong(d) },\n";
            } else {
            // Tipe standar (text, number, dll)
                    $jsCols .= "                { data: '{$f['name']}', defaultContent: '-', className: 'text-nowrap' },\n";
                }
            }
        }

        $code = "<?= \$this->extend('Modules\\Layout\\Views\\main') ?>\n\n";
        $code .= "<?= \$this->section('content') ?>\n";
        $code .= "<div class='container-xl'>\n";
        $code .= "    <div class='card shadow-sm border-0'>\n";
        $code .= "        <div class='card-header'>\n";
        $code .= "            <h3 class='card-title'>DATA " . strtoupper($className) . "</h3>\n";
        $code .= "            <div class='card-actions'>\n";
        $code .= "                <?php if (\$access['can_create'] == 1): ?>\n";
        $code .= "                    <button type='button' class='btn btn-primary btn-pill' onclick='addData()'>\n";
        $code .= "                        <i class='ti ti-plus me-2'></i> Tambah\n";
        $code .= "                    </button>\n";
        $code .= "                <?php endif; ?>\n";
        $code .= "            </div>\n";
        $code .= "        </div>\n";
        $code .= "        <div class='table-responsive p-3'>\n";
        $code .= "            <table id='t-{$lowerClass}' class='table table-vcenter card-table table-striped w-100'>\n";
        $code .= "                <thead>\n";
        $code .= "                    <tr>\n";
        $code .= "                        <th width='5%'>NO</th>\n";
        $code .= "{$th}";
        $code .= "                        <th width='10%' class='text-center'>AKSI</th>\n";
        $code .= "                    </tr>\n";
        $code .= "                </thead>\n";
        $code .= "                <tbody></tbody>\n";
        $code .= "            </table>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n\n";

    // Modals Container
        $code .= "\n";
        $code .= "<div class='modal modal-blur fade' id='modal-form' data-bs-focus='false'>\n";
        $code .= "    <div class='modal-dialog modal-dialog-centered'>\n";
        $code .= "        <div class='modal-content' id='modal-content-area'></div>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n\n";

        $code .= "\n";
        $code .= "<div class='modal modal-blur fade' id='modal-success' tabindex='-1'>\n";
        $code .= "    <div class='modal-dialog modal-sm modal-dialog-centered'>\n";
        $code .= "        <div class='modal-content'>\n";
        $code .= "            <div class='modal-status bg-success'></div>\n";
        $code .= "            <div class='modal-body text-center py-4'>\n";
        $code .= "                <i class='ti ti-circle-check text-success mb-2' style='font-size: 3rem;'></i>\n";
        $code .= "                <h3>Berhasil!</h3>\n";
        $code .= "                <div class='text-secondary' id='success-msg'></div>\n";
        $code .= "            </div>\n";
        $code .= "            <div class='modal-footer'>\n";
        $code .= "                <button class='btn btn-success w-100' data-bs-dismiss='modal'>Selesai</button>\n";
        $code .= "            </div>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n\n";

        $code .= "\n";
        $code .= "<div class='modal modal-blur fade' id='modal-confirm' tabindex='-1'>\n";
        $code .= "    <div class='modal-dialog modal-sm modal-dialog-centered'>\n";
        $code .= "        <div class='modal-content'>\n";
        $code .= "            <div class='modal-status bg-danger'></div>\n";
        $code .= "            <div class='modal-body text-center py-4'>\n";
        $code .= "                <i class='ti ti-alert-triangle text-danger mb-2' style='font-size: 3rem;'></i>\n";
        $code .= "                <h3>Konfirmasi</h3>\n";
        $code .= "                <div class='text-secondary'>Apakah anda yakin ingin menghapus data ini?</div>\n";
        $code .= "            </div>\n";
        $code .= "            <div class='modal-footer'>\n";
        $code .= "                <div class='row w-100 g-2'>\n";
        $code .= "                    <div class='col'><button class='btn w-100' data-bs-dismiss='modal'>Batal</button></div>\n";
        $code .= "                    <div class='col'><button class='btn btn-danger w-100' id='btn-confirm-del'>Ya, Hapus</button></div>\n";
        $code .= "                </div>\n";
        $code .= "            </div>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n";

        $code .= "<?= \$this->endSection() ?>\n\n";

    // SCRIPTS
        $code .= "<?= \$this->section('scripts') ?>\n";
        $code .= "<script>\n";
        $code .= "    var table, deleteId;\n";
        $code .= "    $(document).ready(function() {\n";
        $code .= "        table = $('#t-{$lowerClass}').DataTable({\n";
        $code .= "            processing: true, \n";
        $code .= "            serverSide: true,\n";
        $code .= "            order: [[1, 'asc']],\n";
        $code .= "            ajax: { \n";
        $code .= "                url: '<?= base_url('{$lowerClass}/ajaxData') ?>', \n";
        $code .= "                type: 'POST', \n";
        $code .= "                data: d => { d.<?= csrf_token() ?> = '<?= csrf_hash() ?>'; } \n";
        $code .= "            },\n";
        $code .= "            columns: [\n{$jsCols}";
        $code .= "                { data: 'action', orderable: false, searchable: false, className: 'text-center' }\n";
        $code .= "            ],\n";
        $code .= "            language: { search: '', searchPlaceholder: 'Cari...' }\n";
        $code .= "        });\n";
        $code .= "    });\n\n";

        $code .= "    function addData() { $.get('<?= base_url('{$lowerClass}/create') ?>', res => { $('#modal-content-area').html(res); $('#modal-form').modal('show'); }); }\n";
        $code .= "    function editData(id) { $.get('<?= base_url('{$lowerClass}/edit/') ?>' + id, res => { $('#modal-content-area').html(res); $('#modal-form').modal('show'); }); }\n";
        $code .= "    function deleteData(id) { deleteId = id; $('#modal-confirm').modal('show'); }\n\n";

        $code .= "    $('#btn-confirm-del').on('click', function() { \n";
        $code .= "        $.post('<?= base_url('{$lowerClass}/delete/') ?>' + deleteId, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' }, res => {\n";
        $code .= "            if(res.status === 'success') {\n";
        $code .= "                $('#modal-confirm').modal('hide');\n";
        $code .= "                $('#success-msg').text(res.message);\n";
        $code .= "                $('#modal-success').modal('show');\n";
        $code .= "                table.ajax.reload(null, false);\n";
        $code .= "            } else {\n";
        $code .= "                alert(res.message);\n";
        $code .= "            }\n";
        $code .= "        }, 'json'); \n";
        $code .= "    });\n\n";

        $code .= "    $(document).on('submit', '#form-gen', function(e) {\n";
        $code .= "        e.preventDefault();\n";
        $code .= "        $.ajax({ \n";
        $code .= "            url: $(this).attr('action'), \n";
        $code .= "            type: 'POST', \n";
        $code .= "            data: new FormData(this), \n";
        $code .= "            processData: false, \n";
        $code .= "            contentType: false,\n";
        $code .= "            success: res => {\n";
        $code .= "                if(res.status === 'success') { \n";
        $code .= "                    $('#modal-form').modal('hide'); \n";
        $code .= "                    $('#success-msg').text(res.message); \n";
        $code .= "                    $('#modal-success').modal('show'); \n";
        $code .= "                    table.ajax.reload(null, false); \n";
        $code .= "                } else {\n";
        $code .= "                    // Tampilkan validasi atau error\n";
        $code .= "                    alert(res.message);\n";
        $code .= "                }\n";
        $code .= "            }\n";
        $code .= "        });\n";
        $code .= "    });\n";
        $code .= "    // Inisialisasi Select2 & AddressHelper setiap kali modal ditampilkan\n";
        $code .= "    $(document).on('shown.bs.modal', '#modal-form', function () {\n";
        $code .= "        // 1. Inisialisasi Select2 Standar\n";
        $code .= "        $('.select2-modal').select2({\n";
        $code .= "            theme: 'bootstrap-5',\n";
        $code .= "            width: '100%',\n";
        $code .= "            placeholder: $(this).data('placeholder'),\n";
        $code .= "        });\n\n";

        $code .= "        // 2. Deteksi jika ada field bertipe Address\n";
        $code .= "        if($('[data-address-level]').length) {\n";
        $code .= "            new AddressHelper({\n";
        $code .= "                initValues: {\n";
        $code .= "                    prov: '<?= \$row[\"provinsi\"] ?? \"\" ?>',\n";
        $code .= "                    kab:  '<?= \$row[\"kabupaten\"] ?? \"\" ?>',\n";
        $code .= "                    kec:  '<?= \$row[\"kecamatan\"] ?? \"\" ?>',\n";
        $code .= "                    kel:  '<?= \$row[\"kelurahan\"] ?? \"\" ?>'\n";
        $code .= "                }\n";
        $code .= "            });\n";
        $code .= "        }\n";
        $code .= "    });\n";
        $code .= "</script>\n";
        $code .= "<?= \$this->endSection() ?>";
        return $code;
    }


    private function buildFormView($className, $table, $fields)
    {
        $inputs = "";
        $lowerClass = $this->generateSlug($className);
        foreach ($fields as $f) {
            if (($f['show_form'] ?? 'false') === 'false') continue;
            
            $name  = $f['name']; 
            $label = strtoupper($f['label']); 
            $label2 = ucwords(strtolower($f['label'])); 
            $type  = $f['type'];
            $placeholder = "Masukkan " . $f['label'] . "...";

            // --- LOGIKA BARU: CEK REQUIRED ---
            $isRequired = ($f['required'] ?? 'false') === 'true';
            $reqAttr = $isRequired ? "required" : "";
            $star = $isRequired ? " <span class=\"text-danger\">*</span>" : "";

            $inputs .= "        <div class=\"mb-3\">\n";
            $inputs .= "            <label class=\"form-label\">{$label}{$star}</label>\n"; // Tambah bintang

            if ($type == 'image') {
                $inputs .= "            <input type=\"file\" name=\"{$name}\" class=\"form-control\" accept=\"image/*\" onchange=\"previewImage(this, 'prev-{$name}')\">\n";
                $inputs .= "            <input type=\"hidden\" name=\"old_{$name}\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\">\n";
                $inputs .= "            <div class=\"mt-2 text-center\">\n";
                $inputs .= "                <img id=\"prev-{$name}\" src=\"<?= (isset(\$row) && !empty(\$row['{$name}'])) ? base_url('uploads/'.\$row['{$name}']) : '' ?>\" class=\"img-thumbnail shadow-sm\" style=\"max-height:150px; <?= (isset(\$row) && !empty(\$row['{$name}'])) ? '' : 'display:none' ?>\">\n";
                $inputs .= "            </div>\n";

            } else if ($type == 'file') {
                $inputs .= "            <input type=\"file\" name=\"{$name}\" class=\"form-control\" accept=\"image/*\" onchange=\"previewImage(this, 'prev-{$name}')\" {$reqAttr}>\n";
                $inputs .= "            <input type=\"hidden\" name=\"old_{$name}\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\">\n";
                $inputs .= "            <?php if(isset(\$row) && !empty(\$row['{$name}'])): ?>\n";
                $inputs .= "                <div class=\"mt-1\"><small class=\"text-muted\">File: <a href=\"<?= base_url('uploads/'.\$row['{$name}']) ?>\" target=\"_blank\"><i class=\"ti ti-file-search me-1\"></i> Preview</a></small></div>\n";
                $inputs .= "            <?php endif; ?>\n";

            } else if ($type == 'select_ref') {
                $group = $f['ref_group'] ?? '';
                $inputs .= "            <select name=\"{$name}\" class=\"form-select select2-modal\" data-placeholder=\"-- Pilih {$label2} --\" {$reqAttr}>\n";
                $inputs .= "                <option value=\"\"></option>\n";
                $inputs .= "                <?= getOption('{$group}', \$row['{$name}'] ?? '') ?>\n";
                $inputs .= "            </select>\n";

            } else if ($type == 'select_db') {
                $targetTable = $f['ref_table'] ?? '';
                $inputs .= "            <select name=\"{$name}\" class=\"form-select select2-modal\" data-placeholder=\"-- Pilih {$label2} --\" {$reqAttr}>\n";
                $inputs .= "                <option value=\"\"></option>\n";
                $inputs .= "                <?= getOptionDb('{$targetTable}', \$row['{$name}'] ?? '') ?>\n";
                $inputs .= "            </select>\n";
            } else if ($type == 'address') {
                // Kita berikan ID khusus berdasarkan nama kolom agar JS bisa mengenali hirarkinya
                $inputs .= "            <select name=\"{$name}\" id=\"addr-{$name}\" data-address-level=\"{$name}\" class=\"form-select select2-modal\" data-placeholder=\"-- Pilih {$label2} --\" {$reqAttr}>\n";
                $inputs .= "                <option value=\"\"></option>\n";
                $inputs .= "            </select>\n";
            }else if ($type == 'text_area') { // Sesuaikan dengan value dari index generator Anda
                $inputs .= "            <textarea name=\"{$name}\" class=\"form-control\" rows=\"3\" placeholder=\"{$placeholder}\" {$reqAttr}><?= isset(\$row) ? \$row['{$name}'] : '' ?></textarea>\n";

            } else if ($type == 'number') {
                $inputs .= "            <input type=\"number\" name=\"{$name}\" class=\"form-control\" placeholder=\"{$placeholder}\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\" {$reqAttr}>\n";

            } else if ($type == 'date' || $type == 'date_long') {
                $inputs .= "            <input type=\"date\" name=\"{$name}\" class=\"form-control\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\" {$reqAttr}>\n";

            } else if ($type == 'time') {
                $inputs .= "            <input type=\"time\" name=\"{$name}\" class=\"form-control\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\" {$reqAttr}>\n";

            } else if ($type == 'currency') {
                $val = "<?= isset(\$row) ? \$row['{$name}'] : '' ?>";
                $inputs .= "            <input type=\"text\" name=\"{$name}\" class=\"form-control format-rp\" placeholder=\"{$placeholder}\" value=\"{$val}\" {$reqAttr}>\n";

            }else {
                $inputs .= "            <input type=\"text\" name=\"{$name}\" class=\"form-control\" placeholder=\"{$placeholder}\" value=\"<?= isset(\$row) ? \$row['{$name}'] : '' ?>\" {$reqAttr}>\n";
            }
            
            $inputs .= "        </div>\n";
        }


        return "<form id=\"form-gen\" action=\"<?= isset(\$row) ? base_url('{$lowerClass}/update/'.\$row['id']) : base_url('{$lowerClass}/store') ?>\" method=\"POST\" enctype=\"multipart/form-data\">\n" .
        "    <?= csrf_field() ?>\n" .
        "    <div class=\"modal-header\">\n" .
        "        <h5 class=\"modal-title\"><?= isset(\$row) ? 'Update' : 'Tambah' ?> Data {$className}</h5>\n" .
        "        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button>\n" .
        "    </div>\n" .
        "    <div class=\"modal-body\">\n" .
        "{$inputs}" .
        "    </div>\n" .
        "    <div class=\"modal-footer\">\n" .
        "        <button type=\"button\" class=\"btn me-auto\" data-bs-dismiss=\"modal\">Batal</button>\n" .
        "        <button type=\"submit\" class=\"btn btn-primary\">Simpan Data</button>\n" .
        "    </div>\n" .
        "</form>";
    }

}