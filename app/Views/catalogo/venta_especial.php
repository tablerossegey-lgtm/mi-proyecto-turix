<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">
    <!-- Hero Banner Venta Especial -->
    <div class="venta-especial-hero mb-4 p-4 p-md-5 rounded-4 position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #31103f 100%) !important;">
        <div class="venta-especial-overlay"></div>
        <div class="row align-items-center position-relative z-2">
            <div class="col-lg-8 text-white">
                <span class="badge bg-danger text-uppercase px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm animate-pulse d-inline-flex align-items-center gap-1">
                    <i class="bi bi-rocket-takeoff-fill me-1"></i> Precios Promoción y Especiales
                </span>
                <h1 class="display-5 fw-bold text-white mb-2 font-outfit">
                    <i class="bi bi-rocket-takeoff-fill text-danger me-2"></i> VENTA ESPECIAL
                </h1>
                <p class="lead text-white-50 mb-4" style="max-width: 600px; font-size: 1.05rem;">
                    Aprovecha nuestras promociones exclusivas con precios rebajados por tiempo limitado. ¡Los mejores productos de TurixShop al mejor precio!
                </p>
                <div class="d-flex flex-wrap gap-2 gap-md-3 align-items-center">
                    <span class="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-60 text-warning px-3 py-2 rounded-pill border border-warning border-opacity-25 small fw-semibold">
                        <i class="bi bi-fire text-danger fs-5"></i> Ofertas y Preventa Exclusiva
                    </span>
                    <span class="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-60 text-light px-3 py-2 rounded-pill border border-secondary border-opacity-25 small">
                        <i class="bi bi-shield-check text-success fs-5"></i> Pedidos por WhatsApp
                    </span>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-center position-relative">
                <div class="offer-rocket-badge p-4 text-center rounded-circle mx-auto d-flex flex-column justify-content-center align-items-center shadow-lg">
                    <i class="bi bi-tags-fill display-4 text-warning mb-1"></i>
                    <span class="fw-bold fs-6 text-white text-uppercase" style="letter-spacing: 1px;">Súper</span>
                    <span class="fw-black fs-2 text-warning text-shadow" style="font-weight: 900;">OFERTAS</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda de Ofertas -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 mb-3 mb-md-0">
            <h2 class="fw-bold titulo-seccion mb-0 d-flex align-items-center gap-2" style="color: var(--turix-dark);">
                <i class="bi bi-percent text-danger fs-3"></i> Productos en Promoción
            </h2>
            <div style="width: 70px; height: 4px; background: linear-gradient(90deg, #ff0055, #ff8c00); margin-top: 10px; border-radius: 2px;"></div>
        </div>
        <div class="col-md-6">
            <div class="position-relative">
                <input type="search" class="form-control rounded-pill shadow-sm" placeholder="Buscar en Venta Especial..." name="q"
                    hx-post="<?= base_url('catalogo/buscar-ofertas') ?>" hx-trigger="input changed delay:500ms" hx-target="#productos-grid"
                    hx-swap="innerHTML"
                    style="padding-left: 2.5rem; padding-right: 1rem; border: 1.5px solid #ff8c00; font-size: 0.95rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="position-absolute" style="top: 50%; left: 1rem; transform: translateY(-50%); color: #ff8c00;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>
    </div>

    <!-- Grid de Productos en Oferta -->
    <div class="row g-4" id="productos-grid">
        <?= $this->include('catalogo/_grid_productos') ?>
    </div>

    <div class="row mt-5 mb-4">
        <div class="col-12 text-center">
            <a href="<?= base_url('catalogo') ?>"
                class="btn btn-outline-dark rounded-pill px-5 py-2.5 fw-bold shadow-sm"
                style="border-width: 2px; text-decoration: none;">
                <i class="bi bi-grid-3x3-gap me-2"></i> Ver Todo el Catálogo
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
