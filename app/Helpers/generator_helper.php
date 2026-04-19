<?php

if (!function_exists('generate_module_files')) {
    /**
     * Otomatisasi pembuatan View, Function di Controller, dan Function di Model
     * * @param string $urlInput Contoh: 'users/logs'
     */
    function generate_module_files($urlInput)
    {
        $urlInput = trim($urlInput, '/');
        if (empty($urlInput)) return;

        $parts = explode('/', $urlInput);
        $segment1 = $parts[0];
        $methodName = (isset($parts[1]) && $parts[1] != '') ? $parts[1] : 'index';

        // Transformasi Nama
        $moduleName = str_replace(' ', '', ucwords(str_replace('-', ' ', $segment1)));
        $ajaxMethodName = "ajax" . ucfirst($methodName);

        // Pathing
        $modulePath     = APPPATH . "Modules/{$moduleName}";
        $viewFolderPath = "{$modulePath}/Views";
        $viewFilePath   = "{$viewFolderPath}/{$methodName}.php";
        $controllerPath = "{$modulePath}/Controllers/{$moduleName}.php";
        $modelPath      = "{$modulePath}/Models/{$moduleName}Model.php";

        if (!is_dir($modulePath)) return;

        // --- 1. GENERATE VIEW ---
        if (!is_dir($viewFolderPath)) mkdir($viewFolderPath, 0777, true);
        if (!file_exists($viewFilePath)) {
            $templateView = "<?= \$this->extend('Modules\Layout\Views\main') ?>\n\n";
            $templateView .= "<?= \$this->section('content') ?>\n";
            $templateView .= "<div class='card'>\n";
            $templateView .= "    <div class='card-header'><h3 class='card-title text-uppercase'>Data " . str_replace('-', ' ', $methodName) . "</h3></div>\n";
            $templateView .= "    <div class='card-body'>Halaman otomatis untuk {$methodName}</div>\n";
            $templateView .= "</div>\n";
            $templateView .= "<?= \$this->endSection() ?>\n";
            file_put_contents($viewFilePath, $templateView);
        }

        // --- 2. INJEKSI CONTROLLER ---
        if (file_exists($controllerPath)) {
            $controllerContent = file_get_contents($controllerPath);
            if (strpos($controllerContent, "public function {$methodName}()") === false) {
                $newFunctions = "\n    public function {$methodName}()\n    {\n";
                $newFunctions .= "        \$access = \$this->getAccess();\n";
                $newFunctions .= "        if (\$access['can_read'] != 1) return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak');\n\n";
                $newFunctions .= "        return view('Modules\\{$moduleName}\\Views\\{$methodName}', [\n";
                $newFunctions .= "            'title'  => '" . ucwords(str_replace('-', ' ', $methodName)) . " " . $moduleName . "',\n";
                $newFunctions .= "            'url'    => '{$urlInput}',\n";
                $newFunctions .= "            'access' => \$access\n";
                $newFunctions .= "        ]);\n    }\n\n";

                $newFunctions .= "    public function {$ajaxMethodName}()\n    {\n";
                $newFunctions .= "        if (\$this->request->isAJAX()) {\n";
                $newFunctions .= "            return \$this->model->{$ajaxMethodName}(\$this->request);\n";
                $newFunctions .= "        }\n    }\n";

                $lastBrace = strrpos($controllerContent, '}');
                file_put_contents($controllerPath, substr_replace($controllerContent, $newFunctions . "\n}", $lastBrace));
            }
        }

        // --- 3. INJEKSI MODEL ---
        if (file_exists($modelPath)) {
            $modelContent = file_get_contents($modelPath);
            if (strpos($modelContent, "public function {$ajaxMethodName}(") === false) {
                $newModelMethod = "\n    public function {$ajaxMethodName}(\$request)\n    {\n";
                $newModelMethod .= "        return json_encode(['data' => []]);\n    }\n";

                $lastBraceModel = strrpos($modelContent, '}');
                file_put_contents($modelPath, substr_replace($modelContent, $newModelMethod . "\n}", $lastBraceModel));
            }
        }
    }
}