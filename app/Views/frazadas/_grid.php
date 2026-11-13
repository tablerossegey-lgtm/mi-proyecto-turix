<?php
$desde = ($totalFiltradas > 0) ? (($paginaActual - 1) * $porPagina + 1) : 0;
$hasta = min($paginaActual * $porPagina, $totalFiltradas);
?>
<!-- Resumen de resultados -->
<div
    class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 text-secondary small fw-medium">
    <div>
        <?php if (!empty($busqueda)): ?>
            Resultados para <strong class="text-dark">"<?= esc($busqueda) ?>"</strong>:
        <?php endif; ?>
        Mostrando del <strong class="text-dark fw-bold"><?= $desde ?> al <?= $hasta ?></strong> de <strong
            class="text-dark fw-bold"><?= $totalFiltradas ?></strong> modelos disponibles
        <?php if ($totalFiltradas < $totalFrazadas): ?>
            (filtrados de <?= $totalFrazadas ?> totales)
        <?php endif; ?>
    </div>
    <div class="mt-1 mt-sm-0">
        Página <span class="badge bg-dark text-white px-2.5 py-1 rounded-pill"><?= $paginaActual ?> de
            <?= $totalPaginas ?></span>
    </div>
</div>

<?php if (empty($frazadas)): ?>
    <div class="text-center py-5 my-4 p-4 rounded-4"
        style="background: rgba(0,0,0,0.02); border: 1px dashed rgba(0,0,0,0.15);">
        <i class="bi bi-search text-warning display-4 mb-3 d-block"></i>
        <h5 class="fw-bold text-dark mb-2">No se encontró ningún modelo</h5>
        <p class="text-muted mb-3">Intenta buscar por otro número de modelo (por ejemplo: <span
                class="badge bg-dark">1</span>, <span class="badge bg-dark">45</span> o <span
                class="badge bg-dark">120</span>).</p>
        <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark"
            onclick="limpiarBusquedaFrazadas()">
            <i class="bi bi-arrow-clockwise me-1"></i> Ver todos los modelos
        </button>
    </div>
<?php else: ?>
    <!-- Cuadrícula de Frazadas -->
    <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6">
        <?php foreach ($frazadas as $item):
            $msgWa = "¡Hola TurixShop! Me gustaría pedir información y encargar la Frazada " . $item['titulo'];
            $urlWa = "https://wa.me/" . $telefonoWa . "?text=" . urlencode($msgWa);
            ?>
            <div class="col">
                <div class="card h-100 card-producto card-frazada position-relative shadow-sm"
                    style="background: #121824; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease;">

                    <!-- Badge de Modelo -->
                    <span class="badge bg-warning text-dark position-absolute shadow-sm"
                        style="top: 10px; left: 10px; z-index: 5; font-size: 0.72rem; font-weight: 800; border-radius: 50px; padding: 4px 10px; letter-spacing: 0.5px;">
                        <i class="bi bi-tag-fill me-1"></i><?= esc($item['titulo']) ?>
                    </span>

                    <?php if ($item['categoria'] !== 'General'): ?>
                        <span class="badge bg-info text-dark position-absolute shadow-sm"
                            style="top: 10px; right: 10px; z-index: 5; font-size: 0.65rem; border-radius: 50px;">
                            <?= esc($item['categoria']) ?>
                        </span>
                    <?php endif; ?>

                    <!-- Contenedor de Foto con Zoom al Clic -->
                    <div class="frazada-img-wrapper position-relative"
                        style="height: 220px; overflow: hidden; cursor: pointer; background: #0b0f17; display: flex; align-items: center; justify-content: center;"
                        onclick="abrirModalFrazada('<?= esc($item['url']) ?>', '<?= esc($item['titulo']) ?>', '<?= esc($urlWa) ?>')">
                        <img src="<?= esc($item['url']) ?>" alt="<?= esc($item['titulo']) ?>"
                            class="w-100 h-100 object-fit-cover frazada-thumb" loading="lazy"
                            onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>';">

                        <div class="frazada-overlay d-flex align-items-center justify-content-center">
                            <span class="btn btn-sm btn-light rounded-pill shadow-sm fw-bold px-3">
                                <i class="bi bi-arrows-fullscreen me-1"></i> Ampliar
                            </span>
                        </div>
                    </div>

                    <!-- Pie de la tarjeta -->
                    <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between text-center"
                        style="background: rgba(255,255,255,0.02);">
                        <h6 class="fw-bold text-white mb-2 small text-truncate" title="<?= esc($item['titulo']) ?>">
                            <?= esc($item['titulo']) ?>
                        </h6>
                        <a href="<?= esc($urlWa) ?>" target="_blank" rel="noopener noreferrer"
                            class="btn btn-success btn-sm rounded-pill fw-bold d-inline-flex align-items-center justify-content-center gap-1 w-100 shadow-sm"
                            style="background-color: #25D366; border-color: #25D366; font-size: 0.78rem; padding: 6px 10px;">
                            <i class="bi bi-whatsapp"></i> Encargar
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav aria-label="Navegación de modelos" class="mt-4 pt-2">
            <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">
                <!-- Botón Anterior -->
                <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                    <button type="button" class="page-link rounded-pill px-3 bg-dark text-white border-secondary"
                        onclick="cambiarPaginaFrazadas(<?= $paginaActual - 1 ?>)" <?= ($paginaActual <= 1) ? 'disabled' : '' ?>>
                        <i class="bi bi-chevron-left"></i> Anterior
                    </button>
                </li>

                <?php
                // Rango de páginas inteligentes para no saturar si hay muchas
                $inicio = max(1, $paginaActual - 3);
                $fin = min($totalPaginas, $paginaActual + 3);

                if ($inicio > 1): ?>
                    <li class="page-item">
                        <button type="button" class="page-link rounded-circle bg-dark text-white border-secondary"
                            onclick="cambiarPaginaFrazadas(1)">1</button>
                    </li>
                    <?php if ($inicio > 2): ?>
                        <li class="page-item disabled"><span class="page-link bg-dark text-white-50 border-secondary">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                    <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                        <button type="button"
                            class="page-link rounded-circle <?= ($i === $paginaActual) ? 'bg-warning text-dark border-warning fw-bold' : 'bg-dark text-white border-secondary' ?>"
                            onclick="cambiarPaginaFrazadas(<?= $i ?>)">
                            <?= $i ?>
                        </button>
                    </li>
                <?php endfor; ?>

                <?php if ($fin < $totalPaginas): ?>
                    <?php if ($fin < $totalPaginas - 1): ?>
                        <li class="page-item disabled"><span class="page-link bg-dark text-white-50 border-secondary">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <button type="button" class="page-link rounded-circle bg-dark text-white border-secondary"
                            onclick="cambiarPaginaFrazadas(<?= $totalPaginas ?>)"><?= $totalPaginas ?></button>
                    </li>
                <?php endif; ?>

                <!-- Botón Siguiente -->
                <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                    <button type="button" class="page-link rounded-pill px-3 bg-dark text-white border-secondary"
                        onclick="cambiarPaginaFrazadas(<?= $paginaActual + 1 ?>)" <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>>
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </button>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>