<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <title>Login - SIADU</title>
    <link href="<?= base_url('assets/tabler/dist/css/tabler.min.css') ?>" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root { --tblr-font-sans-serif: 'Inter Var', sans-serif; }
    </style>
</head>
<body class="d-flex flex-column bg-light">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
    <script src="<?= base_url('assets/tabler/dist/js/tabler.min.js') ?>"></script>
</body>
</html>