<?php
// Detectar si estamos en una categoría específica basada en la URL actual o una variable pasada
$uri = service('uri');
$categoriaId = ($uri->getSegment(1) === 'catalogo' && $uri->getSegment(2) === 'categoria') ? $uri->getSegment(3) : null;
// Si la variable $categoria_id fue pasada explícitamente desde el controlador, la usamos:
if (isset($categoria_id)) {
    $categoriaId = $categoria_id;
}
$searchUrl = base_url('catalogo/buscar' . ($categoriaId ? '/' . $categoriaId : ''));
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6 mb-3 mb-md-0">
        <h2 class="fw-bold titulo-seccion mb-0" style="color: var(--turix-dark);"><?= $titulo ?? 'Productos' ?></h2>
        <div style="width: 60px; height: 4px; background: var(--turix-accent); margin-top: 10px; border-radius: 2px;"></div>
    </div>
    <div class="col-md-6">
        <div class="position-relative">
            <input type="search" 
                   class="form-control rounded-pill shadow-sm" 
                   placeholder="Buscar productos..." 
                   name="q"
                   hx-post="<?= $searchUrl ?>" 
                   hx-trigger="input changed delay:500ms" 
                   hx-target="#productos-grid"
                   hx-swap="innerHTML"
                   style="padding-left: 2.5rem; padding-right: 1rem; border: 1px solid #cbd5e1; font-size: 0.95rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="position-absolute" style="top: 50%; left: 1rem; transform: translateY(-50%); color: #64748b;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
    </div>
</div>

<div class="row g-4" id="productos-grid">
    <?= $this->include('catalogo/_grid_productos') ?>
</div>

<div class="row mt-5">
    <div class="col-12 text-center">
        <button class="btn btn-outline-dark rounded-pill px-5 py-2 fw-bold shadow-sm" 
            hx-get="<?= base_url('categorias') ?>"
            hx-target="#main-content"
            style="border-width: 2px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="m15 18-6-6 6-6"/></svg>
            Volver a Categorías
        </button>
    </div>
</div>