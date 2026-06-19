<div class="container-fluid px-0">
    <!-- Hero Banner Premium -->
    <div class="hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Bienvenidos a TurixShop</h1>
            <p class="hero-subtitle">
                Descubre un catálogo lleno de novedades exclusivas, adornos especiales para festividades, soluciones en computación y los mejores accesorios para ti.
            </p>
            <div class="hero-buttons-container d-flex flex-column flex-sm-row flex-md-row gap-2 gap-md-3">
                <a href="<?= base_url('catalogo') ?>" class="btn btn-gradient rounded-pill fw-bold btn-primary-hero">
                    <i class="bi bi-bag"></i> Explorar Catálogo
                </a>
                <a href="#categorias-section" class="btn btn-outline-white rounded-pill fw-bold">
                    <i class="bi bi-grid text-info"></i> Ver Categorías
                </a>
                <a href="#novedades-section" class="btn btn-outline-white rounded-pill fw-bold">
                    <i class="bi bi-stars text-warning"></i> Ver Novedades
                </a>
            </div>
        </div>
    </div>

    <!-- Sección de Categorías Destacadas -->
    <div class="categorias-section" id="categorias-section">
        <h2 class="section-title">📂 EXPLORAR CATEGORÍAS 📂</h2>
        <p class="section-subtitle">Navega por nuestro catálogo organizado según tus intereses</p>

        <?php if (!empty($categorias)): ?>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
                <?php foreach ($categorias as $cat): ?>
                    <?php 
                        $catImageSrc = obtener_ruta_categoria($cat['imagen'] ?? '', $cat['nombre'] ?? '');
                    ?>
                    <div class="col">
                        <div class="cat-grid-card h-100" onclick="window.location.href='<?= base_url('catalogo/categoria/' . $cat['idCategoria']) ?>'">
                            <div class="cat-grid-img-wrapper">
                                <img src="<?= $catImageSrc ?>" 
                                     alt="Categoría <?= esc($cat['nombre']) ?>" 
                                     class="cat-grid-img"
                                     onerror="this.src='<?= base_url('images/categorias/SinCategoria.jpg') ?>'; this.onerror=null;">
                            </div>
                            <div class="cat-grid-body">
                                <h6 class="cat-grid-title"><?= esc($cat['nombre']) ?></h6>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border-0 shadow-sm text-center py-5 rounded-4">
                <h5 class="fw-bold">No hay categorías disponibles</h5>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sección de Novedades -->
    <div class="novedades-container" id="novedades-section">
        <h2 class="section-title">✨ ÚLTIMAS NOVEDADES ✨</h2>
        <p class="section-subtitle">Conoce los productos más nuevos que acabamos de agregar a nuestra colección</p>

        <?php if (!empty($novedades)): ?>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
                <?php foreach ($novedades as $p): ?>
                    <?php 
                        $srcUrl = obtener_ruta_imagen($p['foto'] ?? '', $p['nombre_categoria'] ?? '');
                        $p['foto_url'] = $srcUrl;
                    ?>
                    <div class="col">
                        <div class="card h-100 card-producto novedad-card">
                            <?php if (isset($p['precio_promo']) && $p['precio_promo'] > 0 && $p['precio_promo'] < $p['precio']): ?>
                                <span class="badge bg-danger position-absolute" style="top: 15px; left: 15px; z-index: 10; font-size: 0.65rem; font-weight: bold; border-radius: 50px; padding: 4px 10px; letter-spacing: 0.5px;">PROMO</span>
                            <?php endif; ?>

                            <span class="sku-badge"><?= esc($p['codigo_sku']) ?></span>

                            <div class="contenedor-foto">
                                <img src="<?= $srcUrl ?>"
                                     class="img-fluid foto-producto" 
                                     alt="<?= esc($p['descripcion']) ?>"
                                     onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                                <button type="button" 
                                        class="btn-quick-whatsapp shadow-sm"
                                        title="Agregar al Carrito"
                                        onclick="agregarAlCarritoRapido(<?= esc(json_encode($p)) ?>)">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>

                            <div class="card-body">
                                <h6 class="descripcion-producto" title="<?= esc($p['descripcion']) ?>">
                                    <?= esc($p['descripcion']) ?>
                                </h6>
                                <div class="precio-tag">
                                    <?php if (isset($p['precio_promo']) && $p['precio_promo'] > 0 && $p['precio_promo'] < $p['precio']): ?>
                                        <span class="simbolo-moneda">$</span>
                                        <?= number_format($p['precio_promo'], 2) ?>
                                        <span class="text-white-50 text-decoration-line-through ms-2 fs-6 fw-normal" style="font-size: 0.85rem;">
                                            $<?= number_format($p['precio'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="simbolo-moneda">$</span>
                                        <?= number_format($p['precio'], 2) ?>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-turix w-100 shadow-sm fw-bold" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalDetalle"
                                    hx-get="<?= base_url('catalogo/detalle/' . $p['id']) ?>" 
                                    hx-target="#contenido-modal">
                                    Ver Detalles <i class="bi bi-zoom-in ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="<?= base_url('catalogo') ?>" class="btn btn-warning rounded-pill px-5 py-2.5 fw-bold hover-warning text-dark shadow">
                    <i class="bi bi-grid-3x3-gap text-dark me-2"></i> Ver Catálogo Completo
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-light border-0 shadow-sm text-center py-5 rounded-4">
                <div class="mb-3 text-muted">
                    <i class="bi bi-box-seam fs-1"></i>
                </div>
                <h5 class="fw-bold">No hay novedades por el momento</h5>
                <p class="text-muted mb-0">Vuelve pronto para ver los últimos lanzamientos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
