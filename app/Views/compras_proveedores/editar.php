<div class="modal-header modal-encargo-header py-3 px-4"
    style="border-bottom: 1px solid rgba(255,255,255,0.1);">
    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
        <i class="fas fa-edit text-info"></i> Editar Compra a Proveedor #<?= $compra['idCompraProveedor'] ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>

<form id="formEditarCompra" action="<?= base_url('admin/compras/actualizar/' . $compra['idCompraProveedor']) ?>" method="POST" class="w-100 flex-grow-1 d-flex flex-column"
    style="overflow: hidden;"
    hx-post="<?= base_url('admin/compras/actualizar/' . $compra['idCompraProveedor']) ?>" hx-target="#formEditarCompra" hx-swap="outerHTML" hx-indicator="#loading-indicator">
    <?= csrf_field() ?>
    <div class="modal-body p-4" style="overflow-y: auto;">
        <!-- Datos Generales de la Compra -->
        <h6 class="text-warning fw-bold mb-3"><i class="fas fa-file-invoice"></i> 1. Información General de Factura</h6>
        <div class="row g-3 mb-4 p-3 rounded-4 compra-details-box" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
            <!-- Proveedor (Live Search / Autocomplete) -->
            <div class="col-12 col-md-4">
                <label for="proveedor-search-input-edit" class="form-label modal-encargo-label">Proveedor *</label>
                <input type="hidden" name="idProveedor" id="idProveedorEdit" value="<?= $compra['idProveedor'] ?>" required>
                <div class="proveedor-autocomplete">
                    <input type="text" id="proveedor-search-input-edit"
                        class="form-control modal-encargo-control text-white"
                        placeholder="Escribe para buscar proveedor..." autocomplete="off"
                        spellcheck="false" value="<?= esc($compra['nombre_proveedor']) ?>">
                    <div class="proveedor-autocomplete-list" id="proveedor-autocomplete-list-edit"></div>
                </div>
            </div>

            <!-- Fecha Compra -->
            <div class="col-12 col-md-4">
                <label for="fechaCompraEdit" class="form-label modal-encargo-label">Fecha de Compra *</label>
                <input type="date" class="form-control modal-encargo-control text-white" id="fechaCompraEdit"
                    name="fechaCompra" value="<?= $compra['fechaCompra'] ?>" required>
            </div>

            <!-- Descripcion / Concepto -->
            <div class="col-12 col-md-4">
                <label for="descripcionEdit" class="form-label modal-encargo-label">Descripción General / Comentario</label>
                <input type="text" class="form-control modal-encargo-control text-white" id="descripcionEdit"
                    name="descripcion" placeholder="Ej: Compra de navidad, Lote de plumas, etc." value="<?= esc($compra['descripcion']) ?>">
            </div>

            <!-- Costos Adicionales -->
            <div class="col-12 col-md-6">
                <label for="envio_local_estimado_edit" class="form-label modal-encargo-label">Envío Local o Envío Estimado ($)</label>
                <input type="number" step="0.01" class="form-control modal-encargo-control text-white"
                    id="envio_local_estimado_edit" name="envio_local_estimado" min="0" value="<?= number_format($compra['envio_local_estimado'], 2, '.', '') ?>"
                    onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()">
            </div>

            <div class="col-12 col-md-6">
                <label for="impuesto_importacion_edit" class="form-label modal-encargo-label">Impuesto o Tarifa de Importación ($)</label>
                <input type="number" step="0.01" class="form-control modal-encargo-control text-white"
                    id="impuesto_importacion_edit" name="impuesto_importacion" min="0" value="<?= number_format($compra['impuesto_importacion'], 2, '.', '') ?>"
                    onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()">
            </div>
        </div>

        <!-- Detalle de Productos -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-warning fw-bold mb-0"><i class="fas fa-boxes"></i> 2. Artículos Comprados</h6>
            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-4 fw-bold"
                onclick="agregarFilaEditar()">
                <i class="fas fa-plus"></i> Agregar Artículo
            </button>
        </div>

        <!-- Contenedor de filas dinámicas -->
        <div class="table-responsive border border-secondary border-opacity-25 rounded-4 p-0 mb-3"
            style="background: rgba(255,255,255,0.01);">
            <table class="table table-hover align-middle mb-0 text-white" id="tabla-productos-compra-editar"
                style="min-width: 1350px;">
                <thead class="text-white-50 small"
                    style="background: rgba(255, 255, 255, 0.03); border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th style="width: 260px;">Seleccionar del Inventario</th>
                        <th style="width: 160px;">SKU</th>
                        <th style="width: 220px;">Nombre Artículo</th>
                        <th class="text-center" style="width: 75px;">Cant.</th>
                        <th class="text-end" style="width: 105px;">Costo Prov.</th>
                        <th class="text-center" style="width: 95px;">Margen %</th>
                        <th class="text-end" style="width: 105px;">Sugerido</th>
                        <th class="text-end" style="width: 105px;">Venta Final</th>
                        <th class="text-center" style="width: 170px;">Costo Prorrateado</th>
                        <th class="text-start" style="width: 180px;">Acciones Inv.</th>
                        <th class="text-center" style="width: 50px;">Quitar</th>
                    </tr>
                </thead>
                <tbody id="productos-container-editar">
                    <?php foreach ($detalles as $idx => $d): ?>
                        <tr class="row-producto" id="row_editar_<?= $idx ?>" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="text-center fw-bold text-white-50">
                                <span class="row-index-editar"><?= $idx + 1 ?></span>
                            </td>
                            <td>
                                <input type="hidden" class="hidden-id-producto" name="productos[<?= $idx ?>][id_producto]" value="<?= $d['id_producto'] ?>">
                                <div class="prod-autocomplete">
                                    <input type="text"
                                           class="form-control table-input-compact prod-search-input text-white"
                                           placeholder="Busca por nombre o SKU..."
                                           value="<?= esc($d['nombre']) ?><?= $d['sku'] ? ' (' . esc($d['sku']) . ')' : '' ?>"
                                           autocomplete="off" spellcheck="false">
                                    <div class="prod-autocomplete-list"></div>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control table-input-compact text-white input-sku" name="productos[<?= $idx ?>][sku]" value="<?= esc($d['sku']) ?>" placeholder="Ej: TS-PROD-01" required <?= $d['id_producto'] ? 'readonly' : '' ?>>
                            </td>
                            <td>
                                <input type="text" class="form-control table-input-compact text-white input-nombre" name="productos[<?= $idx ?>][nombre]" value="<?= esc($d['nombre']) ?>" placeholder="Nombre del artículo" required <?= $d['id_producto'] ? 'readonly' : '' ?>>
                            </td>
                            <td>
                                <input type="number" class="form-control table-input-compact text-white text-center input-cantidad" name="productos[<?= $idx ?>][cantidad]" min="1" value="<?= $d['cantidad'] ?>" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()" required>
                            </td>
                            <td>
                                <input type="number" step="0.0001" class="form-control table-input-compact text-white text-end input-precio-proveedor" name="productos[<?= $idx ?>][precio_proveedor]" min="0" value="<?= number_format($d['precio_proveedor'], 4, '.', '') ?>" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()" required>
                            </td>
                            <td>
                                <input type="number" step="0.1" class="form-control table-input-compact text-white text-center input-margen" name="productos[<?= $idx ?>][margen]" min="0" value="<?= number_format($d['margen'], 1, '.', '') ?>" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()">
                            </td>
                            <td>
                                <input type="text" class="form-control table-input-compact text-white-50 text-end input-venta-sugerido" readonly value="$<?= number_format($d['precio_venta_sugerido'], 2, '.', '') ?>">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control table-input-compact text-white text-end input-venta-final" name="productos[<?= $idx ?>][precio_venta_final]" min="0" value="<?= number_format($d['precio_venta_final'], 2, '.', '') ?>">
                            </td>
                            <td class="text-center text-success small">
                                <div class="d-flex flex-column gap-0.5 justify-content-center align-items-center">
                                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">U: <strong class="text-success">$<span class="label-costo-real-unit"><?= number_format($d['costo_real_unit'], 2, '.', '') ?></span></strong></span>
                                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">T: <strong class="text-success">$<span class="label-costo-real-total"><?= number_format($d['costo_real_total'], 2, '.', '') ?></span></strong></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1 align-items-start justify-content-center px-1">
                                    <div class="form-check checkbox-actualizar-inventario-div m-0" id="div_act_editar_<?= $idx ?>" style="display: <?= $d['id_producto'] ? 'block' : 'none' ?>;">
                                        <input class="form-check-input input-actualizar-inventario" type="checkbox" name="productos[<?= $idx ?>][actualizar_inventario]" value="1" checked id="check_act_editar_<?= $idx ?>">
                                        <label class="form-check-label text-success fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_act_editar_<?= $idx ?>">Actualizar precio</label>
                                    </div>
                                    <div class="form-check checkbox-registrar-nuevo-div m-0" id="div_new_editar_<?= $idx ?>" style="display: <?= $d['id_producto'] ? 'none' : 'block' ?>;">
                                        <input class="form-check-input input-registrar-nuevo" type="checkbox" name="productos[<?= $idx ?>][registrar_nuevo_inventario]" value="1" id="check_new_editar_<?= $idx ?>" onchange="toggleRegistrarNuevoEditar(this, <?= $idx ?>)">
                                        <label class="form-check-label text-warning fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_new_editar_<?= $idx ?>">Crear nuevo</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="eliminarFilaEditar(<?= $idx ?>)">
                                    <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Resumen del Pedido en el pie del modal -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 rounded-4 mt-4"
            style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);">
            <div class="text-white-50 small">
                <div>Total Base de Productos: <strong class="text-white">$<span
                            id="total-productos-label-edit">0.00</span></strong></div>
                <div>Envío + Impuesto: <strong class="text-white">$<span
                            id="total-adicionales-label-edit">0.00</span></strong></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-white-50 text-uppercase small">Importe total pagado:</span>
                <span class="fs-3 fw-bold text-success cost-badge" id="total-pagado-label-edit">$0.00</span>
            </div>
        </div>
    </div>

    <div class="modal-footer modal-encargo-footer d-flex gap-2"
        style="border-top: 1px solid rgba(255,255,255,0.1);">
        <button type="button" class="btn btn-outline-light rounded-pill px-4"
            data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow"
            id="btn-submit-compra-edit">
            <i class="fas fa-save me-1"></i> Guardar Cambios
        </button>
    </div>
</form>

<script>
    (function () {
        // Exponer inventario como array JS
        const inventario = <?= json_encode($inventario) ?>;
        let filaIndexEditar = <?= count($detalles) ?>;

        // Inicializar autocompletes en las filas preexistentes
        for (let i = 0; i < filaIndexEditar; i++) {
            initProductoAutocompleteEditar(i);
        }

        // Recalcular montos inicialmente
        recalcularMontosEditar();

        // ── Gestión de los spinners e inputs en el envío ─────────────────────
        const formEditarCompra = document.getElementById('formEditarCompra');
        if (formEditarCompra) {
            formEditarCompra.addEventListener('submit', function (e) {
                const btnSubmit = document.getElementById('btn-submit-compra-edit');
                const rows = document.querySelectorAll('#productos-container-editar .row-producto');

                if (rows.length === 0) {
                    e.preventDefault();
                    alert('Debe agregar al menos un artículo a la compra.');
                    return;
                }

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }

        // ───────────────────────────────────────────────────────────────────────────
        // Autocomplete de producto (filas dinámicas)
        // ───────────────────────────────────────────────────────────────────────────
        function initProductoAutocompleteEditar(index) {
            const row = document.getElementById(`row_editar_${index}`);
            if (!row) return;

            const searchInput = row.querySelector('.prod-search-input');
            const hiddenInput = row.querySelector('.hidden-id-producto');
            const dropdownList = row.querySelector('.prod-autocomplete-list');
            const inputSku = row.querySelector('.input-sku');
            const inputNombre = row.querySelector('.input-nombre');
            const divAct = document.getElementById(`div_act_editar_${index}`);
            const divNew = document.getElementById(`div_new_editar_${index}`);
            const checkNew = document.getElementById(`check_new_editar_${index}`);

            let paActiveIndex = -1;
            let paFiltered = [];

            function phHighlight(text, q) {
                if (!q) return text;
                const i = text.toLowerCase().indexOf(q.toLowerCase());
                if (i === -1) return text;
                return text.slice(0, i)
                    + `<span class="pm-highlight">${text.slice(i, i + q.length)}</span>`
                    + text.slice(i + q.length);
            }

            function renderProdDropdown(query) {
                const q = query.trim().toLowerCase();
                paFiltered = inventario.filter(p =>
                    q === '' ||
                    p.descripcion.toLowerCase().includes(q) ||
                    p.codigo_sku.toLowerCase().includes(q)
                );

                dropdownList.innerHTML = '';
                paActiveIndex = -1;

                // Opción "Producto Libre" siempre en primer lugar
                const libreDiv = document.createElement('div');
                libreDiv.className = 'prod-autocomplete-item libre-item';
                libreDiv.innerHTML = '<i class="fas fa-box-open me-2"></i>Producto Libre (No en inventario)';
                libreDiv.addEventListener('mousedown', (e) => { e.preventDefault(); selectProductoLibre(); });
                dropdownList.appendChild(libreDiv);

                if (paFiltered.length === 0 && q !== '') {
                    const empty = document.createElement('div');
                    empty.className = 'prod-autocomplete-empty';
                    empty.innerHTML = '<i class="fas fa-search me-1"></i>Sin coincidencias en inventario';
                    dropdownList.appendChild(empty);
                } else {
                    paFiltered.forEach((prod, i) => {
                        const div = document.createElement('div');
                        div.className = 'prod-autocomplete-item';
                        div.innerHTML = `${phHighlight(prod.descripcion, q)}<span class="sku-tag">(${phHighlight(prod.codigo_sku, q)})</span>`;
                        div.title = `${prod.descripcion} (${prod.codigo_sku})`;
                        div.addEventListener('mousedown', (e) => { e.preventDefault(); selectProducto(prod); });
                        dropdownList.appendChild(div);
                    });
                }

                dropdownList.classList.add('open');
            }

            function selectProducto(prod) {
                hiddenInput.value = prod.id;
                searchInput.value = `${prod.descripcion} (${prod.codigo_sku})`;
                inputSku.value = prod.codigo_sku;
                inputSku.setAttribute('readonly', 'true');
                inputNombre.value = prod.descripcion;
                inputNombre.setAttribute('readonly', 'true');
                if (divNew) divNew.style.display = 'none';
                if (checkNew) checkNew.checked = false;
                if (divAct) divAct.style.display = 'block';
                const precio = parseFloat(prod.precio) || 0;
                row.querySelector('.input-venta-final').value = precio.toFixed(2);
                dropdownList.classList.remove('open');
                recalcularMontosEditar();
            }

            function selectProductoLibre() {
                hiddenInput.value = '';
                searchInput.value = '';
                inputSku.value = '';
                inputSku.removeAttribute('readonly');
                inputNombre.value = '';
                inputNombre.removeAttribute('readonly');
                if (divNew) divNew.style.display = 'block';
                if (checkNew) checkNew.checked = false;
                if (divAct) divAct.style.display = 'none';
                dropdownList.classList.remove('open');
                searchInput.setAttribute('placeholder', 'Producto Libre — escribe o busca...');
                inputSku.focus();
                recalcularMontosEditar();
            }

            function setPaActive(newIdx) {
                const items = dropdownList.querySelectorAll('.prod-autocomplete-item');
                items.forEach((el, i) => el.classList.toggle('pa-active', i === newIdx));
                if (newIdx >= 0 && items[newIdx]) items[newIdx].scrollIntoView({ block: 'nearest' });
                paActiveIndex = newIdx;
            }

            function closeDrop() { dropdownList.classList.remove('open'); paActiveIndex = -1; }

            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                renderProdDropdown(this.value);
            });

            searchInput.addEventListener('focus', function () {
                renderProdDropdown(this.value);
            });

            searchInput.addEventListener('keydown', function (e) {
                const items = dropdownList.querySelectorAll('.prod-autocomplete-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setPaActive(Math.min(paActiveIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setPaActive(Math.max(paActiveIndex - 1, 0));
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (paActiveIndex === 0) { selectProductoLibre(); return; }
                    const realIdx = paActiveIndex - 1; // offset por el item Libre
                    if (realIdx >= 0 && paFiltered[realIdx]) selectProducto(paFiltered[realIdx]);
                } else if (e.key === 'Escape') {
                    closeDrop();
                }
            });

            document.addEventListener('click', function (e) {
                if (!row.contains(e.target)) closeDrop();
            });
        }

        // Agregar nueva fila de producto (Edición)
        window.agregarFilaEditar = function () {
            const container = document.getElementById('productos-container-editar');
            const index = filaIndexEditar++;

            const filaHtml = `
        <tr class="row-producto" id="row_editar_${index}" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
            <!-- # Índice -->
            <td class="text-center fw-bold text-white-50">
                <span class="row-index-editar"></span>
            </td>

            <!-- Seleccionar Inventario -->
            <td>
                <input type="hidden" class="hidden-id-producto" name="productos[${index}][id_producto]" value="">
                <div class="prod-autocomplete">
                    <input type="text"
                           class="form-control table-input-compact prod-search-input text-white"
                           placeholder="Busca por nombre o SKU..."
                           autocomplete="off" spellcheck="false">
                    <div class="prod-autocomplete-list"></div>
                </div>
            </td>

            <!-- SKU -->
            <td>
                <input type="text" class="form-control table-input-compact text-white input-sku" name="productos[${index}][sku]" placeholder="Ej: TS-PROD-01" required>
            </td>

            <!-- Nombre -->
            <td>
                <input type="text" class="form-control table-input-compact text-white input-nombre" name="productos[${index}][nombre]" placeholder="Nombre del artículo" required>
            </td>

            <!-- Cantidad -->
            <td>
                <input type="number" class="form-control table-input-compact text-white text-center input-cantidad" name="productos[${index}][cantidad]" min="1" value="1" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()" required>
            </td>

            <!-- Costo Proveedor -->
            <td>
                <input type="number" step="0.0001" class="form-control table-input-compact text-white text-end input-precio-proveedor" name="productos[${index}][precio_proveedor]" min="0" value="0.00" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()" required>
            </td>

            <!-- Margen -->
            <td>
                <input type="number" step="0.1" class="form-control table-input-compact text-white text-center input-margen" name="productos[${index}][margen]" min="0" value="30" onkeyup="recalcularMontosEditar()" onchange="recalcularMontosEditar()">
            </td>

            <!-- Venta Sugerido -->
            <td>
                <input type="text" class="form-control table-input-compact text-white-50 text-end input-venta-sugerido" readonly value="$0.00">
            </td>

            <!-- Venta Final -->
            <td>
                <input type="number" step="0.01" class="form-control table-input-compact text-white text-end input-venta-final" name="productos[${index}][precio_venta_final]" min="0" value="0.00">
            </td>

            <!-- Costo Prorrateado -->
            <td class="text-center text-success small">
                <div class="d-flex flex-column gap-0.5 justify-content-center align-items-center">
                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">U: <strong class="text-success">$<span class="label-costo-real-unit">0.00</span></strong></span>
                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">T: <strong class="text-success">$<span class="label-costo-real-total">0.00</span></strong></span>
                </div>
            </td>

            <!-- Acciones Inv. -->
            <td>
                <div class="d-flex flex-column gap-1 align-items-start justify-content-center px-1">
                    <div class="form-check checkbox-actualizar-inventario-div m-0" id="div_act_editar_${index}" style="display: none;">
                        <input class="form-check-input input-actualizar-inventario" type="checkbox" name="productos[${index}][actualizar_inventario]" value="1" checked id="check_act_editar_${index}">
                        <label class="form-check-label text-success fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_act_editar_${index}">Actualizar precio</label>
                    </div>
                    <div class="form-check checkbox-registrar-nuevo-div m-0" id="div_new_editar_${index}" style="display: block;">
                        <input class="form-check-input input-registrar-nuevo" type="checkbox" name="productos[${index}][registrar_nuevo_inventario]" value="1" id="check_new_editar_${index}" onchange="toggleRegistrarNuevoEditar(this, ${index})">
                        <label class="form-check-label text-warning fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_new_editar_${index}">Crear nuevo</label>
                    </div>
                </div>
            </td>

            <!-- Quitar -->
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="eliminarFilaEditar(${index})">
                    <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                </button>
            </td>
        </tr>
        `;

            container.insertAdjacentHTML('beforeend', filaHtml);
            actualizarNumeracionEditar();
            recalcularMontosEditar();
            // Inicializar autocomplete en la fila recién creada
            initProductoAutocompleteEditar(index);
        };

        // Eliminar fila de producto (Edición)
        window.eliminarFilaEditar = function (id) {
            const row = document.getElementById(`row_editar_${id}`);
            if (row) {
                row.remove();
                actualizarNumeracionEditar();
                recalcularMontosEditar();
            }
        };

        // Actualizar números de filas (Edición)
        function actualizarNumeracionEditar() {
            const indexes = document.querySelectorAll('.row-index-editar');
            indexes.forEach((el, idx) => {
                el.innerText = idx + 1;
            });
        }

        // Al checkear "Registrar como nuevo" en producto libre
        window.toggleRegistrarNuevoEditar = function (checkboxEl, idx) {
            const divAct = document.getElementById(`div_act_editar_${idx}`);
            if (checkboxEl.checked) {
                if (divAct) divAct.style.display = "block";
            } else {
                if (divAct) divAct.style.display = "none";
            }
        };

        // Recalcular montos en tiempo real (Edición)
        function recalcularMontosEditar() {
            const rows = document.querySelectorAll('#productos-container-editar .row-producto');
            let total_productos = 0;

            // 1. Calcular base
            rows.forEach(row => {
                const qty = parseInt(row.querySelector('.input-cantidad').value) || 0;
                const price = parseFloat(row.querySelector('.input-precio-proveedor').value) || 0;
                total_productos += qty * price;
            });

            const envio = parseFloat(document.getElementById('envio_local_estimado_edit').value) || 0;
            const impuesto = parseFloat(document.getElementById('impuesto_importacion_edit').value) || 0;

            const total_pagado = total_productos + envio + impuesto;
            const total_adicionales = envio + impuesto;

            // Calcular el factor multiplicador de prorrateo
            const factor = total_productos > 0 ? (total_pagado / total_productos) : 1;

            // 2. Aplicar prorrateo y calcular sugeridos por fila
            rows.forEach(row => {
                const qty = parseInt(row.querySelector('.input-cantidad').value) || 0;
                const price = parseFloat(row.querySelector('.input-precio-proveedor').value) || 0;
                const margen = parseFloat(row.querySelector('.input-margen').value) || 0;

                const costo_real_unit = price * factor;
                const costo_real_total = costo_real_unit * qty;
                const venta_sugerido = costo_real_unit * (1 + margen / 100);

                row.querySelector('.label-costo-real-unit').innerText = costo_real_unit.toFixed(2);
                row.querySelector('.label-costo-real-total').innerText = costo_real_total.toFixed(2);
                row.querySelector('.input-venta-sugerido').value = '$' + venta_sugerido.toFixed(2);

                const inputFinal = row.querySelector('.input-venta-final');
                if (inputFinal.value === "" || parseFloat(inputFinal.value) === 0 || inputFinal.dataset.touched !== "true") {
                    inputFinal.value = venta_sugerido.toFixed(2);
                }
            });

            // 3. Imprimir totales generales
            document.getElementById('total-productos-label-edit').innerText = total_productos.toFixed(2);
            document.getElementById('total-adicionales-label-edit').innerText = total_adicionales.toFixed(2);
            document.getElementById('total-pagado-label-edit').innerText = '$' + total_pagado.toFixed(2);
        }
        window.recalcularMontosEditar = recalcularMontosEditar;

        // Marcar que el usuario modificó manualmente el precio final
        const containerEditar = document.getElementById('productos-container-editar');
        if (containerEditar) {
            containerEditar.addEventListener('input', function (e) {
                if (e.target && e.target.classList.contains('input-venta-final')) {
                    e.target.dataset.touched = "true";
                }
            });
        }

        // ─── Autocomplete de Proveedor (Modal Editar Compra) ───────────────────
        (function () {
            const proveedoresList = <?= json_encode(array_map(fn($p) => [
                'id' => $p['idProveedor'],
                'nombre' => $p['nombre']
            ], $proveedores)) ?>;

            const searchInput = document.getElementById('proveedor-search-input-edit');
            const hiddenInput = document.getElementById('idProveedorEdit');
            const dropdownList = document.getElementById('proveedor-autocomplete-list-edit');

            if (!searchInput || !hiddenInput || !dropdownList) return;

            let activeIndex = -1;
            let filteredItems = [];

            function highlight(text, query) {
                if (!query) return text;
                const idx = text.toLowerCase().indexOf(query.toLowerCase());
                if (idx === -1) return text;
                return text.slice(0, idx)
                    + '<span class="match-highlight">' + text.slice(idx, idx + query.length) + '</span>'
                    + text.slice(idx + query.length);
            }

            function renderDropdown(query) {
                const q = query.trim().toLowerCase();
                filteredItems = q === ''
                    ? proveedoresList
                    : proveedoresList.filter(p => p.nombre.toLowerCase().includes(q));

                dropdownList.innerHTML = '';
                activeIndex = -1;

                if (filteredItems.length === 0) {
                    dropdownList.innerHTML = '<div class="proveedor-autocomplete-empty"><i class="fas fa-search me-1"></i>Sin resultados</div>';
                } else {
                    filteredItems.forEach(function (p, i) {
                        const div = document.createElement('div');
                        div.className = 'proveedor-autocomplete-item';
                        div.innerHTML = highlight(p.nombre, q);
                        div.dataset.id = p.id;
                        div.dataset.nombre = p.nombre;
                        div.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            selectProveedor(p.id, p.nombre);
                        });
                        dropdownList.appendChild(div);
                    });
                }

                dropdownList.classList.add('open');
            }

            function selectProveedor(id, nombre) {
                hiddenInput.value = id;
                searchInput.value = nombre;
                searchInput.classList.remove('is-invalid-custom');
                dropdownList.classList.remove('open');
                dropdownList.innerHTML = '';
                activeIndex = -1;
            }

            function closeDropdown() {
                dropdownList.classList.remove('open');
                activeIndex = -1;
            }

            function setActiveItem(newIndex) {
                const items = dropdownList.querySelectorAll('.proveedor-autocomplete-item');
                items.forEach((el, i) => {
                    el.classList.toggle('active', i === newIndex);
                    if (i === newIndex) el.scrollIntoView({ block: 'nearest' });
                });
                activeIndex = newIndex;
            }

            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                renderDropdown(this.value);
            });

            searchInput.addEventListener('focus', function () {
                if (this.value === '') renderDropdown('');
                else renderDropdown(this.value);
            });

            searchInput.addEventListener('keydown', function (e) {
                const items = dropdownList.querySelectorAll('.proveedor-autocomplete-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActiveItem(Math.min(activeIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActiveItem(Math.max(activeIndex - 1, 0));
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && filteredItems[activeIndex]) {
                        const p = filteredItems[activeIndex];
                        selectProveedor(p.id, p.nombre);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !dropdownList.contains(e.target)) {
                    closeDropdown();
                    if (searchInput.value !== '' && hiddenInput.value === '') {
                        searchInput.classList.add('is-invalid-custom');
                    }
                }
            });

            const formEditar = document.getElementById('formEditarCompra');
            if (formEditar) {
                formEditar.addEventListener('submit', function (e) {
                    if (!hiddenInput.value) {
                        e.preventDefault();
                        searchInput.classList.add('is-invalid-custom');
                        searchInput.focus();
                        searchInput.setAttribute('placeholder', '⚠ Selecciona un proveedor de la lista');
                        return;
                    }
                }, true);
            }
        })();
    })();
</script>
