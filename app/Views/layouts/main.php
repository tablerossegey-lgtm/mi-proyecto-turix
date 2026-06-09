<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TurixShop | Catálogo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>">
    <?= $this->renderSection('styles') ?>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon_turix.ico') ?>">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/carrito.js?v=1.4') ?>"></script>
    <script>
        (function() {
            // Delegación de eventos para modalDetalle al ocultarse
            document.addEventListener('hidden.bs.modal', function (e) {
                if (e.target.id === 'modalDetalle') {
                    const contenidoModal = document.getElementById('contenido-modal');
                    if (contenidoModal) {
                        contenidoModal.innerHTML = `
                            <div class="p-5 text-center text-white">
                                <div class="spinner-border text-warning" role="status"></div>
                                <p class="mt-2">Cargando información del producto...</p>
                            </div>
                        `;
                    }
                }
            });

            // Lógica del botón Scroll-To-Top (delegada)
            window.addEventListener('scroll', () => {
                const btnScrollTop = document.getElementById('btnScrollTop');
                if (btnScrollTop) {
                    if (window.scrollY > 300) {
                        btnScrollTop.classList.add('show');
                    } else {
                        btnScrollTop.classList.remove('show');
                    }
                }
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('#btnScrollTop');
                if (btn) {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            // Limpiar estados de Bootstrap (dropdowns y collapses) antes del swap de HTMX
            document.addEventListener('htmx:beforeSwap', function() {
                if (typeof bootstrap !== 'undefined') {
                    const openDropdowns = document.querySelectorAll('.dropdown-toggle.show');
                    openDropdowns.forEach(btn => {
                        const dropdown = bootstrap.Dropdown.getInstance(btn);
                        if (dropdown) {
                            dropdown.hide();
                        }
                    });

                    const openCollapses = document.querySelectorAll('.navbar-collapse.show');
                    openCollapses.forEach(collapseEl => {
                        const collapse = bootstrap.Collapse.getInstance(collapseEl);
                        if (collapse) {
                            collapse.hide();
                        }
                    });
                }
            });

            // Sincronizar clases de body y contador tras transiciones de HTMX
            document.addEventListener('htmx:afterSwap', function() {
                if (typeof actualizarContadorCart === 'function') {
                    actualizarContadorCart();
                }

                if (document.querySelector('.styles-admin-theme')) {
                    document.body.classList.add('admin-body');
                } else {
                    document.body.classList.remove('admin-body');
                }

                // Inicializar y mostrar toasts tras transiciones de HTMX
                if (typeof bootstrap !== 'undefined') {
                    const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
                    toasts.forEach(toastEl => {
                        const toast = new bootstrap.Toast(toastEl);
                        toast.show();
                        toastEl.addEventListener('hidden.bs.toast', () => {
                            toastEl.remove();
                        });
                    });
                }
            });
        })();
    </script>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" hx-boost="true">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= base_url() ?>">
                <img src="<?= base_url('images/logoTurix.png') ?>" alt="Logo TurixShop" class="logo-navbar">
                <span>TURIX<span style="color: var(--turix-yellow);">SHOP</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Enlaces del Público -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center ms-lg-3 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('') || url_is('/')) ? 'active fw-bold text-warning' : 'text-white-50' ?>"
                            id="nav-link-inicio"
                            href="<?= base_url() ?>">
                            <i class="bi bi-house me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('catalogo') || strpos(current_url(), 'catalogo') !== false) ? 'active fw-bold text-warning' : 'text-white-50' ?>"
                            id="nav-link-catalogo"
                            href="<?= base_url('catalogo') ?>">
                            <i class="bi bi-bag me-1"></i> Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 nav-cart-btn" href="#" data-bs-toggle="modal"
                            data-bs-target="#modalCarrito" onclick="renderCarritoModal()">
                            <i class="bi bi-cart3 me-1"></i> Carrito
                            <span class="cart-badge" style="display: none;">0</span>
                        </a>
                    </li>
                </ul>

                <!-- Enlaces Administrativos -->
                <?php if (session()->get('isLoggedIn')): ?>
                    <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center">
                        <div class="dropdown">
                            <button
                                class="btn btn-outline-warning btn-sm dropdown-toggle rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-2"
                                type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock"></i> Admin
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 p-2 mt-2 custom-admin-dropdown"
                                aria-labelledby="adminDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-gallery"
                                        id="admin-item-gallery"
                                        href="<?= base_url('admin/productos') ?>">
                                        <i class="bi bi-images"></i> Panel Galería
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-orders"
                                        id="admin-item-orders"
                                        href="<?= base_url('admin/encargos') ?>">
                                        <i class="bi bi-card-list"></i> Pedidos Encargados
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-accounts"
                                        id="admin-item-accounts"
                                        href="<?= base_url('admin/cuentas') ?>">
                                        <i class="bi bi-wallet2"></i> Cuentas Clientes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-caja"
                                        id="admin-item-caja"
                                        href="<?= base_url('admin/caja') ?>">
                                        <i class="bi bi-cash-coin"></i> Consultar mi Caja
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-semillas"
                                        id="admin-item-semillas"
                                        href="<?= base_url('admin/semillas') ?>">
                                        <i class="bi bi-tree"></i> Venta Snacks
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-compras"
                                        id="admin-item-compras"
                                        href="<?= base_url('admin/compras') ?>">
                                        <i class="bi bi-cart-check"></i> Compras Proveedores
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider border-secondary opacity-25 my-2">
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 rounded admin-item-logout"
                                        id="admin-item-logout"
                                        href="<?= base_url('logout') ?>"
                                        hx-boost="false">
                                        <i class="bi bi-box-arrow-right"></i> Salir
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4" id="main-content" style="flex: 1;">
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

    <!-- Modal de Carrito -->
    <div class="modal fade" id="modalCarrito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg"
                style="background-color: #1a252f; color: #ffffff; border-radius: 20px;">
                <div class="modal-header border-bottom border-secondary border-opacity-25 p-4">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-cart3 text-warning"></i> Tu Carrito de Compras
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 400px; overflow-y: auto;" id="lista-carrito-modal">
                    <!-- Los productos se cargarán dinámicamente aquí -->
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4 flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center w-100 p-3 rounded-3"
                        style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.05); margin-bottom: 0.5rem;">
                        <span class="fw-bold text-uppercase"
                            style="font-size: 0.85rem; letter-spacing: 0.5px; color: rgba(255, 255, 255, 0.6);">Total
                            estimado:</span>
                        <span class="text-white fs-3" id="total-carrito-modal"
                            style="font-family: 'Outfit', sans-serif; font-weight: 800;">$0.00</span>
                    </div>
                    <div class="d-grid gap-2 w-100">
                        <button type="button"
                            class="btn btn-whatsapp-premium fw-bold py-3 shadow d-flex align-items-center justify-content-center gap-2"
                            id="btn-enviar-pedido-whatsapp" onclick="enviarPedidoWhatsApp()">
                            <i class="fab fa-whatsapp fs-5"></i> Confirmar Pedido por WhatsApp
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-white border-secondary py-2"
                            data-bs-dismiss="modal">
                            Seguir Comprando
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-auto border-top">
        <p class="text-muted small">
            &copy; <?= date('Y') ?> TurixShop
            <a href="<?= base_url('login') ?>" class="text-secondary ms-2 opacity-50 text-decoration-none"
                title="Administración">
                <i class="bi bi-lock-fill" style="font-size: 0.8rem;"></i>
            </a>
        </p>
    </footer>

    <!-- Botón Scroll-To-Top Flotante -->
    <button type="button" class="btn btn-warning rounded-circle btn-scroll-top" id="btnScrollTop"
        aria-label="Ir arriba">
        <i class="bi bi-arrow-up"></i>
    </button>

    <?= $this->renderSection('scripts') ?>
</body>

</html>