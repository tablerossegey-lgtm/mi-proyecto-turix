<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Navegación de páginas">
    <ul class="pagination pagination-premium justify-content-center m-0 py-3">
        <!-- Primero -->
        <?php if ($pager->getCurrentPageNumber() > 1) : ?>
            <li class="page-item">
                <a class="page-link page-link-premium" href="<?= $pager->getFirst() ?>" hx-get="<?= $pager->getFirst() ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML" aria-label="Primero" title="Primera Página">
                    <i class="fas fa-angles-left"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link page-link-premium"><i class="fas fa-angles-left"></i></span>
            </li>
        <?php endif ?>

        <!-- Anterior -->
        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="page-item">
                <a class="page-link page-link-premium" href="<?= $pager->getPreviousPage() ?>" hx-get="<?= $pager->getPreviousPage() ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML" aria-label="Anterior" title="Página Anterior">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link page-link-premium"><i class="fas fa-angle-left"></i></span>
            </li>
        <?php endif ?>

        <!-- Páginas individuales -->
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <?php if ($link['active']): ?>
                    <span class="page-link page-link-premium"><?= $link['title'] ?></span>
                <?php else: ?>
                    <a class="page-link page-link-premium" href="<?= $link['uri'] ?>" hx-get="<?= $link['uri'] ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML">
                        <?= $link['title'] ?>
                    </a>
                <?php endif ?>
            </li>
        <?php endforeach ?>

        <!-- Siguiente -->
        <?php if ($pager->hasNextPage()) : ?>
            <li class="page-item">
                <a class="page-link page-link-premium" href="<?= $pager->getNextPage() ?>" hx-get="<?= $pager->getNextPage() ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML" aria-label="Siguiente" title="Página Siguiente">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link page-link-premium"><i class="fas fa-angle-right"></i></span>
            </li>
        <?php endif ?>

        <!-- Último -->
        <?php if ($pager->getCurrentPageNumber() < $pager->getPageCount()) : ?>
            <li class="page-item">
                <a class="page-link page-link-premium" href="<?= $pager->getLast() ?>" hx-get="<?= $pager->getLast() ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML" aria-label="Último" title="Última Página">
                    <i class="fas fa-angles-right"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link page-link-premium"><i class="fas fa-angles-right"></i></span>
            </li>
        <?php endif ?>
    </ul>
</nav>
