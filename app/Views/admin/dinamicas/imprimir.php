<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Participantes - <?= esc($dinamica['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            color: #111827;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .no-print-bar {
            background: #1f222b;
            color: #fff;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        .slips-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 0 12px 24px;
        }
        .slip-box {
            border: 1px dashed #64748b;
            background: #ffffff;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8px 10px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print-bar {
                display: none !important;
            }
            .slips-grid {
                padding: 0;
                gap: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar d-flex justify-content-between align-items-center">
        <div>
            <strong><?= esc($dinamica['nombre']) ?></strong>
            <span class="ms-2 badge bg-warning text-dark"><?= count($lineas) ?> participaciones elegibles</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning btn-sm fw-bold" onclick="window.print()">
                Imprimir nombres
            </button>
            <a href="<?= base_url('admin/dinamicas/detalle/' . $dinamica['id']) ?>" class="btn btn-outline-light btn-sm">
                Volver
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <?php if (empty($lineas)): ?>
            <div class="alert alert-warning text-center m-4">
                No hay participantes elegibles para imprimir en esta dinámica.
            </div>
        <?php else: ?>
            <div class="slips-grid">
                <?php foreach ($lineas as $nombreLinea): ?>
                    <div class="slip-box">
                        <?= esc($nombreLinea) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
