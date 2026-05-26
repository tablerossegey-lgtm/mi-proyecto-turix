<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TurixShop | Catálogo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>">
    <?= $this->renderSection('styles') ?>
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
            <a class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-2" href="<?= base_url('admin/productos') ?>">
                <i class="fas fa-images"></i> Panel Galería
            </a>
        </div>
    </nav>

    <div class="container mt-4" id="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="contenido-modal" style="background-color: #1a252f;">
                <div class="p-5 text-center text-white">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Cargando información del producto...</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-5 border-top">
        <p class="text-muted small">&copy; <?= date('Y') ?> TurixShop - Catálogo</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Limpiar el contenido del modal al cerrarse para evitar que se muestre información del producto anterior
        document.getElementById('modalDetalle').addEventListener('hidden.bs.modal', function () {
            document.getElementById('contenido-modal').innerHTML = `
                <div class="p-5 text-center text-white">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Cargando información del producto...</p>
                </div>
            `;
        });
    </script>
</body>

</html>