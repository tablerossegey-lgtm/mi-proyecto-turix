<?php

namespace App\Models;

use CodeIgniter\Model;

class DinamicaPremioModel extends Model
{
    protected $table            = 't_dinamica_premios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dinamica',
        'orden',
        'nombre_publico',
        'descripcion_admin',
        'imagen',
        'id_producto',
        'valor_estimado',
        'observaciones',
        'estado_entrega',
        'inventario_descontado',
    ];

    protected $useTimestamps = false;

    public function obtenerPorDinamica(int $idDinamica): array
    {
        return $this->db->table('t_dinamica_premios pr')
            ->select('pr.*, i.codigo_sku, i.descripcion AS producto_nombre, i.stock AS producto_stock,
                g.id AS id_ganador, g.id_participante AS ganador_id_participante, g.id_cliente AS ganador_id_cliente,
                g.nombre_ganador, g.numero_participacion, g.fecha_hora_sorteo, g.modalidad_sorteo AS ganador_modalidad,
                g.enlace_resultado, g.evidencia_sorteo, g.observaciones AS ganador_observaciones,
                g.fecha_notificacion AS ganador_fecha_notificacion, g.fecha_entrega AS ganador_fecha_entrega,
                g.usuario_responsable AS ganador_usuario_responsable, g.evidencia_entrega, g.observaciones_entrega')
            ->join('t_inventario i', 'i.id = pr.id_producto', 'left')
            ->join('t_dinamica_ganadores g', 'g.id_premio = pr.id', 'left')
            ->where('pr.id_dinamica', $idDinamica)
            ->orderBy('pr.orden', 'ASC')
            ->orderBy('pr.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
