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

    <?php if (!empty($ofertas)): ?>
    <!-- Sección de Venta Especial (Ofertas Destacadas) -->
    <div class="venta-especial-home-section mb-5 p-4 p-md-5 rounded-4 shadow-lg position-relative" id="venta-especial-section" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%) !important; color: #ffffff !important; border: 1.5px solid rgba(239, 68, 68, 0.4) !important;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 position-relative z-2">
            <div>
                <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase me-2 mb-2 animate-pulse d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-fire me-1"></i> Precios Especiales y Promociones
                </span>
                <h2 class="section-title text-start mb-1 font-outfit" style="color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.6);"><i class="bi bi-rocket-takeoff-fill text-danger me-2"></i> VENTA ESPECIAL</h2>
                <p class="mb-0" style="color: #cbd5e1 !important; font-size: 1rem; font-weight: 500;">Productos con descuentos irresistibles y precios en promoción por tiempo limitado</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="<?= base_url('venta-especial') ?>" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold text-dark shadow hover-warning">
                    Ver Todas las Ofertas <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-6">
            <?php foreach ($ofertas as $p): ?>
                <?php 
                    preparar_producto_para_cliente($p);
                    $srcUrl = obtener_ruta_imagen($p['foto'] ?? '', $p['nombre_categoria'] ?? '');
                    $p['foto_url'] = $srcUrl;
                    $descuentoPorcentaje = ($p['precio'] > 0) ? round((($p['precio'] - $p['precio_promo']) / $p['precio']) * 100) : 0;
                ?>
                <div class="col">
                    <div class="card h-100 card-producto novedad-card border-danger border-opacity-50">
                        <?php if (!empty($p['es_proximamente']) && !empty($p['fecha_inicio_promo'])): ?>
                            <span class="badge bg-warning text-dark position-absolute shadow-sm" style="top: 12px; left: 12px; z-index: 10; font-size: 0.63rem; font-weight: 800; border-radius: 50px; padding: 4px 8px; letter-spacing: 0.3px;">
                                <i class="bi bi-clock-history me-1"></i> INICIA <?= date('d/m', strtotime($p['fecha_inicio_promo'])) ?> (-<?= $descuentoPorcentaje ?>%)
                            </span>
                        <?php elseif ($descuentoPorcentaje > 0): ?>
                            <span class="badge bg-danger position-absolute shadow-sm" style="top: 12px; left: 12px; z-index: 10; font-size: 0.68rem; font-weight: 800; border-radius: 50px; padding: 4px 9px; letter-spacing: 0.5px;">
                                -<?= $descuentoPorcentaje ?>% OFF
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger position-absolute shadow-sm" style="top: 12px; left: 12px; z-index: 10; font-size: 0.65rem; font-weight: bold; border-radius: 50px; padding: 4px 10px;">OFERTA</span>
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
                                <span class="simbolo-moneda">$</span>
                                <?= number_format($p['precio_promo'], 2) ?>
                                <span class="text-white-50 text-decoration-line-through ms-2 fs-6 fw-normal" style="font-size: 0.8rem;">
                                    $<?= number_format($p['precio'], 2) ?>
                                </span>
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
    </div>
    <?php endif; ?>

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
                        preparar_producto_para_cliente($p);
                        $srcUrl = obtener_ruta_imagen($p['foto'] ?? '', $p['nombre_categoria'] ?? '');
                        $p['foto_url'] = $srcUrl;
                    ?>
                    <div class="col">
                        <div class="card h-100 card-producto novedad-card">
                            <?php if (isset($p['precio_promo']) && $p['precio_promo'] > 0 && $p['precio_promo'] < $p['precio']): ?>
                                <span class="badge bg-danger position-absolute" style="top: 15px; left: 15px; z-index: 10; font-size: 0.65rem; font-weight: bold; border-radius: 50px; padding: 4px 10px; letter-spacing: 0.5px;">OFERTA</span>
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
