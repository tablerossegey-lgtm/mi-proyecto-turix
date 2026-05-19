<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TurixShop | Catálogo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon_turix.ico') ?>">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
</head>

<body>

    <nav class="navbar navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= base_url() ?>">
                <img src="<?= base_url('images/logoTurix.png') ?>" alt="Logo TurixShop" class="logo-navbar">
                <span>TURIX<span style="color: var(--turix-yellow);">SHOP</span></span>
            </a>
        </div>
    </nav>

    <div class="container mt-4" id="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <footer class="text-center py-4 mt-5 border-top">
        <p class="text-muted small">&copy; <?= date('Y') ?> TurixShop - Catálogo</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>