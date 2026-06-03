// turixshop - Carrito de Compras Local & Despacho por WhatsApp

document.addEventListener('DOMContentLoaded', () => {
    actualizarContadorCart();
});

// Obtener carrito de localStorage
function obtenerCarrito() {
    try {
        const cart = localStorage.getItem('turix_cart');
        return cart ? JSON.parse(cart) : [];
    } catch (e) {
        console.error("Error al acceder a localStorage", e);
        return [];
    }
}

// Guardar carrito en localStorage y actualizar contadores
function guardarCarrito(carrito) {
    try {
        localStorage.setItem('turix_cart', JSON.stringify(carrito));
        actualizarContadorCart();
    } catch (e) {
        console.error("Error al guardar en localStorage", e);
    }
}

// Actualizar el número de productos en el badge del Navbar
function actualizarContadorCart() {
    const carrito = obtenerCarrito();
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const badges = document.querySelectorAll('.cart-badge');
    
    badges.forEach(badge => {
        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'inline-flex';
            // Agregar pequeña animación de pulso
            badge.classList.remove('badge-pulse');
            void badge.offsetWidth; // Trigger reflow
            badge.classList.add('badge-pulse');
        } else {
            badge.textContent = '0';
            badge.style.display = 'none';
        }
    });
}

// Agregar producto rápido desde la tarjeta (botón flotante)
function agregarAlCarritoRapido(producto) {
    const item = {
        id: parseInt(producto.id),
        descripcion: producto.descripcion,
        precio: parseFloat(producto.precio),
        foto: producto.foto_url || '',
        sku: producto.codigo_sku || '',
        nombre_categoria: producto.nombre_categoria || '',
        cantidad: 1
    };

    let carrito = obtenerCarrito();
    const indice = carrito.findIndex(i => i.id === item.id);

    if (indice !== -1) {
        carrito[indice].cantidad += 1;
    } else {
        carrito.push(item);
    }

    guardarCarrito(carrito);
    mostrarToastNotificacion(item.descripcion);
}

// Agregar producto con cantidad seleccionada desde la modal de detalle
function agregarAlCarritoDetalle(productoId, descripcion, precio, foto, sku, categoria) {
    const inputCant = document.getElementById('detalle-cantidad-input');
    const cantidad = inputCant ? parseInt(inputCant.value) : 1;

    const item = {
        id: parseInt(productoId),
        descripcion: descripcion,
        precio: parseFloat(precio),
        foto: foto,
        sku: sku,
        nombre_categoria: categoria,
        cantidad: cantidad
    };

    let carrito = obtenerCarrito();
    const indice = carrito.findIndex(i => i.id === item.id);

    if (indice !== -1) {
        carrito[indice].cantidad += cantidad;
    } else {
        carrito.push(item);
    }

    guardarCarrito(carrito);
    mostrarToastNotificacion(item.descripcion);

    // Cerrar el modal de detalles
    const modalElement = document.getElementById('modalDetalle');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }
}

// Mostrar notificación Toast
function mostrarToastNotificacion(nombreProducto) {
    // Verificar si ya existe el contenedor de toasts, sino crearlo
    let toastContainer = document.getElementById('toast-cart-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-cart-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1090';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div>
                        <strong>¡Agregado al carrito!</strong><br>
                        <span class="small text-white-50">${nombreProducto.substring(0, 35)}${nombreProducto.length > 35 ? '...' : ''}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    // Eliminar el nodo después de ocultarse
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// Modificar cantidad desde el modal del carrito
function cambiarCantidadCart(id, delta) {
    let carrito = obtenerCarrito();
    const indice = carrito.findIndex(i => i.id === id);

    if (indice !== -1) {
        carrito[indice].cantidad += delta;
        if (carrito[indice].cantidad <= 0) {
            carrito.splice(indice, 1);
        }
        guardarCarrito(carrito);
        renderCarritoModal();
    }
}

// Eliminar producto del carrito
function eliminarDelCarrito(id) {
    let carrito = obtenerCarrito();
    carrito = carrito.filter(i => i.id !== id);
    guardarCarrito(carrito);
    renderCarritoModal();
}

// Renderizar la lista de productos dentro del modal del carrito
function renderCarritoModal() {
    const listContainer = document.getElementById('lista-carrito-modal');
    const totalContainer = document.getElementById('total-carrito-modal');
    const btnSendContainer = document.getElementById('btn-enviar-pedido-whatsapp');
    
    if (!listContainer) return;

    const carrito = obtenerCarrito();

    if (carrito.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-5 text-white-50">
                <i class="bi bi-cart-x fs-1 mb-3 d-block text-muted"></i>
                <h6 class="fw-bold text-white">Tu carrito está vacío</h5>
                <p class="small mb-0">Agrega productos del catálogo para armar tu pedido.</p>
            </div>
        `;
        totalContainer.textContent = '$0.00';
        if (btnSendContainer) {
            btnSendContainer.setAttribute('disabled', 'disabled');
            btnSendContainer.classList.add('opacity-50');
        }
        return;
    }

    if (btnSendContainer) {
        btnSendContainer.removeAttribute('disabled');
        btnSendContainer.classList.remove('opacity-50');
    }

    let html = '';
    let totalSum = 0;

    carrito.forEach(item => {
        const itemTotal = item.precio * item.cantidad;
        totalSum += itemTotal;

        // Resolver la imagen
        const fallbackSrc = window.location.origin + '/mi-proyecto-turix/public/uploads/SinImagen.png';
        const imageSrc = item.foto || fallbackSrc;

        html += `
            <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 border border-secondary border-opacity-25" style="background: #0f172a; padding-right: 1.5rem !important;">
                <!-- Producto Info -->
                <div class="d-flex align-items-center gap-2" style="max-width: 45%; flex-grow: 1; min-width: 0;">
                    <div class="rounded overflow-hidden bg-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;">
                        <img src="${imageSrc}" 
                             alt="${item.descripcion}" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain;"
                             onerror="this.src='${fallbackSrc}';">
                    </div>
                    <div class="overflow-hidden" style="min-width: 0;">
                        <h6 class="text-white mb-0 small text-truncate fw-semibold" title="${item.descripcion}">${item.descripcion}</h6>
                        <span class="text-warning small font-monospace d-block" style="font-size: 0.72rem; line-height: 1.2;">SKU: ${item.sku}</span>
                    </div>
                </div>
                
                <!-- Selector Cantidad -->
                <div class="d-flex align-items-center border border-secondary border-opacity-50 rounded" style="background: rgba(0,0,0,0.2); height: 32px; flex-shrink: 0;">
                    <button class="btn btn-sm btn-link text-white-50 px-2 py-0 border-0" onclick="cambiarCantidadCart(${item.id}, -1)">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span class="px-1 text-white small fw-bold" style="min-width: 18px; text-align: center;">${item.cantidad}</span>
                    <button class="btn btn-sm btn-link text-white-50 px-2 py-0 border-0" onclick="cambiarCantidadCart(${item.id}, 1)">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>

                <!-- Precio y Quitar -->
                <div class="text-end" style="min-width: 80px; flex-shrink: 0;">
                    <span class="text-warning small fw-bold d-block">$${itemTotal.toFixed(2)}</span>
                    <button class="btn btn-link text-danger p-0 border-0 text-decoration-none" style="font-size: 0.75rem;" onclick="eliminarDelCarrito(${item.id})">
                        <i class="bi bi-trash"></i> Quitar
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
    totalContainer.textContent = '$' + totalSum.toFixed(2);
}

// Enviar pedido consolidado por WhatsApp
function enviarPedidoWhatsApp() {
    const carrito = obtenerCarrito();
    if (carrito.length === 0) return;

    let mensaje = "Hola Turixshop, me interesa realizar el siguiente pedido:\n\n";
    let totalSum = 0;

    carrito.forEach(item => {
        const lineTotal = item.precio * item.cantidad;
        totalSum += lineTotal;
        mensaje += `*${item.cantidad}x* ${item.descripcion}\n`;
        mensaje += `  └ [SKU: ${item.sku}] ($${item.precio.toFixed(2)} c/u) = *$${lineTotal.toFixed(2)}*\n\n`;
    });

    mensaje += `*Total del Pedido: $${totalSum.toFixed(2)}*\n\n`;
    mensaje += "Quedo atento a la confirmación de disponibilidad y detalles de pago. ¡Gracias!";

    const whatsappNumber = "529995441466";
    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(mensaje)}`;
    
    // Abrir WhatsApp en nueva ventana
    window.open(whatsappUrl, '_blank');

    // Vaciar el carrito tras el pedido
    guardarCarrito([]);
    
    // Cerrar el modal del carrito
    const modalElement = document.getElementById('modalCarrito');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }
}
