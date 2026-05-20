<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $index => $p): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card h-100 card-producto">
                
                <span class="sku-badge"><?= esc($p['codigo_sku']) ?></span>

                <div class="contenedor-foto">
                    <?php 
                        $categoriaFolder = isset($p['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($p['nombre_categoria']))) : '';
                        $rutaImagen = $p['foto'] ? "uploads/{$categoriaFolder}/" . $p['foto'] : 'images/categorias/SinCategoria.png';
                    ?>
                    <img src="<?= base_url($rutaImagen) ?>"
                        class="img-fluid foto-producto" alt="<?= esc($p['descripcion']) ?>">
                </div>

                <div class="card-body">
                    <h6 class="descripcion-producto" title="<?= esc($p['descripcion']) ?>">
                        <?= esc($p['descripcion']) ?>
                    </h6>

                    <div class="precio-tag">
                        <span style="font-size: 1rem; opacity: 0.7;">$</span>
                        <?= number_format($p['precio'], 2) ?>
                    </div>

                    <button class="btn btn-turix w-100 shadow-sm"
                        hx-get="<?= base_url('catalogo/detalle/' . $p['id']) ?>" 
                        hx-target="#main-content"
                        style="transition: all 0.3s ease;">
                        <span>Ver Detalles</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning opacity-50"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h5 class="fw-bold">No hay productos disponibles</h5>
            <p class="text-muted mb-0">Estamos actualizando nuestro stock. Vuelve pronto.</p>
        </div>
    </div>
<?php endif; ?>
