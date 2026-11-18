<?php

namespace App\Models;

use CodeIgniter\Model;

class DinamicaGanadorModel extends Model
{
    protected $table            = 't_dinamica_ganadores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dinamica',
        'id_premio',
        'id_participante',
        'id_cliente',
        'nombre_ganador',
        'numero_participacion',
        'fecha_hora_sorteo',
        'modalidad_sorteo',
        'enlace_resultado',
        'evidencia_sorteo',
        'observaciones',
        'fecha_notificacion',
        'fecha_entrega',
        'usuario_responsable',
        'evidencia_entrega',
        'observaciones_entrega',
    ];

    protected $useTimestamps = false;

    public function obtenerPorDinamica(int $idDinamica): array
    {
        return $this->db->table('t_dinamica_ganadores g')
            ->select('g.*, pr.orden AS premio_orden, pr.nombre_publico AS premio_nombre_publico, pr.descripcion_admin AS premio_descripcion_admin, pr.estado_entrega, pr.id_producto, pr.inventario_descontado, p.numero_control, p.telefono')
            ->join('t_dinamica_premios pr', 'pr.id = g.id_premio', 'inner')
            ->join('t_dinamica_participantes p', 'p.id = g.id_participante', 'inner')
            ->where('g.id_dinamica', $idDinamica)
            ->orderBy('pr.orden', 'ASC')
            ->get()
            ->getResultArray();
    }
}
