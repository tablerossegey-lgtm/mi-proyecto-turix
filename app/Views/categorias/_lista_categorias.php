<!-- Contenedor principal con clase personalizada -->
<div class="contenedor-categorias shadow-sm">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark mb-0">Explorar Categorías</h2>
            <div style="width: 50px; height: 4px; background: var(--turix-accent); margin-top: 10px; border-radius: 2px;"></div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($categorias as $cat): ?>
            <div class="col-12 col-md-6 col-lg-3">
                <!-- Tarjeta de categoría con clases de diseño -->
                <div class="card h-100 border-0 card-categoria">

                    <div class="contenedor-imagen-categoria position-relative">
                        <img src="<?= obtener_ruta_categoria($cat['imagen'] ?? '', $cat['nombre'] ?? '') ?>"
                            class="img-fluid foto-categoria" alt="<?= esc($cat['nombre']) ?>"
                            onerror="this.src='<?= base_url('images/categorias/SinCategoria.jpg') ?>'; this.onerror=null;">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                            <h6 class="text-white fw-bold mb-0 text-uppercase small" style="letter-spacing: 1px;"><?= esc($cat['nombre']) ?></h6>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <button class="btn btn-turix w-100 py-2"
                            hx-get="<?= base_url('catalogo/categoria/' . $cat['idCategoria']) ?>" 
                            hx-target="#main-content"
                            hx-push-url="true">
                            Explorar
                        </button>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
