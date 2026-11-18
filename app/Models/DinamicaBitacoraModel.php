<?php

namespace App\Models;

use CodeIgniter\Model;

class DinamicaBitacoraModel extends Model
{
    protected $table            = 't_dinamicas_bitacora';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dinamica',
        'id_participante',
        'id_premio',
        'accion',
        'motivo',
        'detalle',
        'usuario_id',
        'usuario_nombre',
    ];

    protected $useTimestamps = false;

    public function registrar(array $datos): bool
    {
        $session = session();
        $usuarioId = $datos['usuario_id'] ?? $session->get('id');
        $usuarioNombre = $datos['usuario_nombre']
            ?? ($session->get('nombre_completo') ?: $session->get('nombre_usuario') ?: 'Sistema');

        return (bool) $this->insert([
            'id_dinamica'     => (int) $datos['id_dinamica'],
            'id_participante' => isset($datos['id_participante']) ? (int) $datos['id_participante'] : null,
            'id_premio'       => isset($datos['id_premio']) ? (int) $datos['id_premio'] : null,
            'accion'          => (string) $datos['accion'],
            'motivo'          => $datos['motivo'] ?? null,
            'detalle'         => $datos['detalle'] ?? null,
            'usuario_id'      => $usuarioId ? (int) $usuarioId : null,
            'usuario_nombre'  => (string) $usuarioNombre,
        ]);
    }

    public function obtenerPorDinamica(int $idDinamica, int $limit = 50): array
    {
        return $this->where('id_dinamica', $idDinamica)
            ->orderBy('creado_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
