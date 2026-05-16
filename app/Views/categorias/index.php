<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Contenedor principal con clase personalizada -->
<div class="contenedor-categorias shadow-sm">

    <div class="row g-4">
        <?php foreach ($categorias as $cat): ?>
            <div class="col-12 col-md-6 col-lg-3">
                <!-- Tarjeta de categoría con clases de diseño -->
                <div class="card h-100 border-0 shadow-sm card-categoria">

                    <div class="contenedor-imagen-categoria">
                        <img src="<?= base_url('images/categorias/' . ($cat['imagen'] ?: 'sinImagen.png')) ?>"
                            class="img-fluid foto-categoria" alt="<?= esc($cat['nombre']) ?>">
                    </div>

                    <div class="card-body p-0 mt-2">
                        <button class="btn btn-turix text-uppercase fw-bold w-100 py-2"
                            hx-get="<?= base_url('catalogo/categoria/' . $cat['idCategoria']) ?>" hx-target="#main-content"
                            hx-push-url="true">
                            <?= esc($cat['nombre']) ?>
                        </button>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>