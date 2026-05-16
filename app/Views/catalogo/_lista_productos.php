<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold titulo-seccion text-white">Productos de la Categoría</h3>
        <hr class="separador-turix">
    </div>
</div>

<div class="row g-4">
    <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $p): ?>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 card-producto">

                    <div class="contenedor-foto">
                        <?php 
                            $categoriaFolder = isset($p['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($p['nombre_categoria']))) : '';
                            $rutaImagen = $p['foto'] ? "uploads/{$categoriaFolder}/" . $p['foto'] : 'images/categorias/SinCategoria.png';
                        ?>
                        <img src="<?= base_url($rutaImagen) ?>"
                            class="img-fluid foto-producto" alt="Producto">
                    </div>

                    <div class="card-body d-flex flex-column">
                        <small class="sku-texto text-uppercase">
                            <?= esc($p['codigo_sku']) ?>
                        </small>
                        <h6 class="card-title descripcion-producto">
                            <?= esc($p['descripcion']) ?>
                        </h6>

                        <div class="mt-auto">
                            <p class="h5 mb-3 precio-texto">$
                                <?= number_format($p['precio'], 2) ?>
                            </p>

                            <button class="btn btn-turix w-100 shadow-sm"
                                hx-get="<?= base_url('catalogo/detalle/' . $p['id']) ?>" hx-target="#main-content">
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                No hay productos asignados a esta categoría en este momento.
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-12 text-center">
        <button class="btn btn-outline-light rounded-pill px-4 fw-bold shadow-sm" hx-get="<?= base_url('categorias') ?>"
            hx-target="#main-content">
            ← Volver a Categorías
        </button>
    </div>
</div>