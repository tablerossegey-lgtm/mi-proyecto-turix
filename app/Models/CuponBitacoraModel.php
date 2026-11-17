<?php

namespace App\Models;

use CodeIgniter\Model;

class CuponBitacoraModel extends Model
{
    protected $table            = 't_cupones_bitacora';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cupon',
        'id_cliente',
        'accion',
        'folio_compra',
        'id_compra',
        'monto_descuento',
        'detalle',
        'usuario_id',
        'usuario_nombre',
    ];

    protected $useTimestamps = false;

    /**
     * Registra un evento en la bitácora de auditoría.
     */
    public function registrar(array $datos): bool
    {
        $session = session();
        $usuarioId = $datos['usuario_id'] ?? $session->get('id');
        $usuarioNombre = $datos['usuario_nombre']
            ?? ($session->get('nombre_completo') ?: $session->get('nombre_usuario') ?: 'Sistema');

        return (bool)$this->insert([
            'id_cupon'        => $datos['id_cupon'] ?? null,
            'id_cliente'      => (int)$datos['id_cliente'],
            'accion'          => (string)$datos['accion'],
            'folio_compra'    => $datos['folio_compra'] ?? null,
            'id_compra'       => $datos['id_compra'] ?? null,
            'monto_descuento' => $datos['monto_descuento'] ?? null,
            'detalle'         => $datos['detalle'] ?? null,
            'usuario_id'      => $usuarioId ? (int)$usuarioId : null,
            'usuario_nombre'  => (string)$usuarioNombre,
        ]);
    }
}

