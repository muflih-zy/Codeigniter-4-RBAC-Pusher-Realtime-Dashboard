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
        $query = $this->db->table('sc_variable')
            ->select('varGroup')
            ->distinct()
            ->get();

        $groups = [];
        foreach ($query->getResult() as $row) {
            $groups[] = $row->varGroup;
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

    // --- GENERATOR MODEL ---
    private function buildModel($className, $table, $fields, $pk)
    {
        $allowed = [];
        foreach ($fields as $f) { 
            if (!empty($f['name']) && $f['name'] !== $pk) $allowed[] = $f['name']; 
        }
        $allowedStr = "'" . implode("', '", $allowed) . "'";

        $code = "<?php\n\nnamespace Modules\\{$className}\\Models;\n\n";
        $code .= "use CodeIgniter\Model;\n\n";
        $code .= "class {$className}Model extends Model\n{\n";
        $code .= "    protected \$table            = '{$table}';\n";
        $code .= "    protected \$primaryKey       = '{$pk}';\n";
        $code .= "    protected \$allowedFields    = [{$allowedStr}];\n";
        $code .= "    protected \$useTimestamps    = true;\n";
        $code .= "    protected \$createdField     = 'created_at';\n";
        $code .= "    protected \$updatedField     = 'updated_at';\n";
        $code .= "}\n";
        return $code;
    }

    // --- GENERATOR CONTROLLER ---
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
        $code .= "        if (!has_access(\$this->moduleUrl)) {\n";
        $code .= "            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function index() {\n";
        $code .= "        return view('Modules\\{$className}\\Views\\index', ['title' => 'Kelola {$className}', 'url' => \$this->moduleUrl]);\n";
        $code .= "    }\n\n";
        $code .= "    public function ajaxData() {\n";
        $code .= "        \$builder = \$this->model->select('{$selectStr}');\n";
        $code .= "        return DataTable::of(\$builder)->addNumbering('no')->add('action', function(\$row) {\n";
        $code .= "            return '<div class=\"btn-list flex-nowrap\">\n";
        $code .= "                        <button class=\"btn btn-sm btn-primary\" onclick=\"editData(' . \$row->{$pk} . ')\"><i class=\"ti ti-edit\"></i></button>\n";
        $code .= "                        <button class=\"btn btn-sm btn-danger\" onclick=\"deleteData(' . \$row->{$pk} . ')\"><i class=\"ti ti-trash\"></i></button>\n";
        $code .= "                    </div>';\n";
        $code .= "        })->toJson(true);\n";
        $code .= "    }\n\n";
        $code .= "    public function create() { return view('Modules\\{$className}\\Views\\form', ['url' => \$this->moduleUrl]); }\n\n";
        $code .= "    public function edit(\$id) {\n";
        $code .= "        \$data = \$this->model->find(\$id);\n";
        $code .= "        return view('Modules\\{$className}\\Views\\form', ['row' => \$data, 'url' => \$this->moduleUrl]);\n";
        $code .= "    }\n\n";
        $code .= "    public function store() {\n";
        $code .= "        if (\$this->model->save(\$this->request->getPost())) {\n";
        $code .= "            \$this->notify('{$table}');\n";
        $code .= "            ActivityLogModel::saveLog('INSERT', \"Menambah {$className}\", 'ti ti-plus', 'success');\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function update(\$id) {\n";
        if($pk !== 'id') $code .= "        \$data = \$this->request->getPost(); \$data['{$pk}'] = \$id;\n";
        $code .= "        if (\$this->model->update(\$id, \$this->request->getPost())) {\n";
        $code .= "            \$this->notify('{$table}');\n";
        $code .= "            ActivityLogModel::saveLog('UPDATE', \"Update {$className} ID: \$id\", 'ti ti-edit', 'info');\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data diperbarui']);\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function delete(\$id) {\n";
        $code .= "        if (\$this->model->delete(\$id)) {\n";
        $code .= "            \$this->notify('{$table}');\n";
        $code .= "            ActivityLogModel::saveLog('DELETE', \"Hapus {$className} ID: \$id\", 'ti ti-trash', 'danger');\n";
        $code .= "            return \$this->response->setJSON(['status' => 'success', 'message' => 'Data dihapus']);\n";
        $code .= "        }\n";
        $code .= "    }\n";
        $code .= "}\n";
        return $code;
    }

    // --- GENERATOR VIEW INDEX ---
    private function buildIndexView($className, $table, $fields, $pk)
    {
        $code = "<?= \$this->extend('Modules\Layout\Views\main') ?>\n\n";
        $code .= "<?= \$this->section('content') ?>\n";
        $code .= "<div class=\"container-xl\">\n";
        $code .= "    <div class=\"card shadow-sm border-0\">\n";
        $code .= "        <div class=\"card-header d-flex justify-content-between\">\n";
        $code .= "            <h3 class=\"card-title text-primary\">Data {$className}</h3>\n";
        $code .= "            <button onclick=\"addData()\" class=\"btn btn-primary btn-pill\"><i class=\"ti ti-plus me-1\"></i> Tambah</button>\n";
        $code .= "        </div>\n";
        $code .= "        <div class=\"card-body p-0\">\n";
        $code .= "            <div class=\"table-responsive\">\n";
        $code .= "                <table id=\"table-{$className}\" class=\"table card-table table-vcenter w-100\">\n";
        $code .= "                    <thead><tr><th>NO</th>\n";
        foreach ($fields as $f) {
            if ($f['show_table'] === 'true') $code .= "                        <th>" . strtoupper($f['label']) . "</th>\n";
        }
        $code .= "                    <th class=\"text-center\">AKSI</th></tr></thead><tbody></tbody>\n";
        $code .= "                </table>\n";
        $code .= "            </div>\n";
        $code .= "        </div>\n";
        $code .= "    </div>\n</div>\n\n";
        $code .= "<?= \$this->section('scripts') ?>\n<script>\n";
        $code .= "    var table = $('#table-{$className}').DataTable({\n";
        $code .= "        processing: true, serverSide: true, ajax: '<?= base_url(\$url . \"/ajaxData\") ?>',\n";
        $code .= "        order: [[1, 'desc']],\n";
        $code .= "        columns: [{data:'no',data: 'no',orderable: false,searchable: false },\n";
        foreach ($fields as $f) {
            if ($f['show_table'] === 'true') $code .= "            {data:'{$f['name']}'},\n";
        }
        $code .= "        {data:'action', className:'text-center'}]\n    });\n";
        $code .= "    function addData() { window.location.href = '<?= base_url(\$url . \"/create\") ?>'; }\n";
        $code .= "    function editData(id) { window.location.href = '<?= base_url(\$url . \"/edit/\") ?>' + id; }\n";
        $code .= "    function deleteData(id) { if(confirm('Hapus data?')) $.post('<?= base_url(\$url . \"/delete/\") ?>' + id, { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' }, function() { table.ajax.reload(); }); }\n";
        $code .= "</script>\n<?= \$this->endSection() ?>\n<?= \$this->endSection() ?>\n";
        return $code;
    }

    // --- GENERATOR VIEW FORM ---
    private function buildFormView($className, $table, $fields, $pk)
    {
        $code = "<?= \$this->extend('Modules\Layout\Views\main') ?>\n\n";
        $code .= "<?= \$this->section('content') ?>\n<div class=\"container-xl\">\n";
        $code .= "    <form method=\"POST\" action=\"<?= isset(\$row) ? base_url(\$url . '/update/' . \$row['{$pk}']) : base_url(\$url . '/store') ?>\">\n";
        $code .= "        <?= csrf_field() ?>\n        <?php if(isset(\$row)): ?><input type=\"hidden\" name=\"{$pk}\" value=\"<?= \$row['{$pk}'] ?>\"><?php endif; ?>\n";
        $code .= "        <div class=\"card\"><div class=\"card-body\">\n";
        foreach ($fields as $f) {
            if ($f['show_form'] !== 'true' || $f['name'] == $pk) continue;
            $code .= "            <div class=\"mb-3\">\n                <label class=\"form-label\">{$f['label']}</label>\n";
            $val = "<?= isset(\$row) ? \$row['{$f['name']}'] : '' ?>";
            if ($f['type'] === 'text_area') $code .= "                <textarea name=\"{$f['name']}\" class=\"form-control\">{$val}</textarea>\n";
            elseif ($f['type'] === 'number') $code .= "                <input type=\"number\" name=\"{$f['name']}\" class=\"form-control\" value=\"{$val}\">\n";
            elseif ($f['type'] === 'date') $code .= "                <input type=\"date\" name=\"{$f['name']}\" class=\"form-control\" value=\"{$val}\">\n";
            else $code .= "                <input type=\"text\" name=\"{$f['name']}\" class=\"form-control\" value=\"{$val}\">\n";
            $code .= "            </div>\n";
        }
        $code .= "        </div><div class=\"card-footer text-end\"><a href=\"<?= base_url(\$url) ?>\" class=\"btn btn-link\">Batal</a><button type=\"submit\" class=\"btn btn-primary\">Simpan</button></div></div>\n";
        $code .= "    </form>\n</div>\n<?= \$this->endSection() ?>\n";
        return $code;
    }
}