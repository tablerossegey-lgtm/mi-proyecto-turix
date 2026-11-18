<?php

namespace App\Models;

use CodeIgniter\Model;

class DinamicaModel extends Model
{
    protected $table            = 't_dinamicas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion',
        'imagen_promo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_sorteo',
        'hora_sorteo',
        'observaciones',
        'modalidad_sorteo',
        'plataforma_digital',
        'url_plataforma',
        'tipo_requisito',
        'importe_minimo',
        'max_participaciones_cliente',
        'max_premios_cliente',
        'permitir_clientes_ocasionales',
        'permitir_registro_manual',
        'requiere_compra_liquidada',
        'plantilla_whatsapp',
        'estado',
        'listado_cerrado',
        'cerrado_en',
        'cerrado_por_id',
        'cerrado_por_nombre',
        'resolucion_cierre',
        'creado_por',
    ];

    protected $useTimestamps = false;

    /**
     * Obtiene el listado de dinámicas con conteo de participantes elegibles y premios.
     */
    public function obtenerListadoConConteos(array $filtros = []): array
    {
        $builder = $this->db->table('t_dinamicas d')
            ->select('d.*,
                (SELECT COUNT(*) FROM t_dinamica_participantes p WHERE p.id_dinamica = d.id AND p.estado = "elegible") AS total_participantes,
                (SELECT COALESCE(SUM(p.num_participaciones), 0) FROM t_dinamica_participantes p WHERE p.id_dinamica = d.id AND p.estado = "elegible") AS total_oportunidades,
                (SELECT COUNT(*) FROM t_dinamica_premios pr WHERE pr.id_dinamica = d.id) AS total_premios,
                (SELECT COUNT(*) FROM t_dinamica_premios pr WHERE pr.id_dinamica = d.id AND pr.estado_entrega != "entregado") AS premios_pendientes')
            ->orderBy('d.fecha_inicio', 'DESC')
            ->orderBy('d.id', 'DESC');

        if (!empty($filtros['q'])) {
            $builder->like('d.nombre', trim((string) $filtros['q']));
        }
        if (!empty($filtros['anio'])) {
            $anio = (int) $filtros['anio'];
            $builder->groupStart()
                ->where('YEAR(d.fecha_inicio)', $anio)
                ->orWhere('YEAR(d.fecha_sorteo)', $anio)
                ->groupEnd();
        }
        if (!empty($filtros['estado'])) {
            $builder->where('d.estado', $filtros['estado']);
        }
        if (!empty($filtros['modalidad'])) {
            $builder->where('d.modalidad_sorteo', $filtros['modalidad']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Indicadores generales del panel principal.
     */
    public function obtenerIndicadoresPanel(): array
    {
        $hoy = date('Y-m-d');

        $activas = (int) $this->db->table('t_dinamicas')
            ->where('estado', 'activa')
            ->countAllResults();

        $proximosSorteos = (int) $this->db->table('t_dinamicas')
            ->whereIn('estado', ['programada', 'activa', 'participaciones_cerradas'])
            ->where('fecha_sorteo >=', $hoy)
            ->countAllResults();

        $finalizadas = (int) $this->db->table('t_dinamicas')
            ->where('estado', 'finalizada')
            ->countAllResults();

        $totalParticipantesActivas = (int) $this->db->table('t_dinamica_participantes p')
            ->join('t_dinamicas d', 'd.id = p.id_dinamica', 'inner')
            ->where('d.estado', 'activa')
            ->where('p.estado', 'elegible')
            ->countAllResults();

        $premiosPendientes = (int) $this->db->table('t_dinamica_premios pr')
            ->join('t_dinamicas d', 'd.id = pr.id_dinamica', 'inner')
            ->where('d.estado !=', 'cancelada')
            ->where('pr.estado_entrega !=', 'entregado')
            ->countAllResults();

        return [
            'activas'                     => $activas,
            'proximos_sorteos'            => $proximosSorteos,
            'finalizadas'                 => $finalizadas,
            'total_participantes_activas' => $totalParticipantesActivas,
            'premios_pendientes'          => $premiosPendientes,
        ];
    }

    /**
     * Años disponibles para el filtro del panel principal.
     */
    public function obtenerAniosDisponibles(): array
    {
        $anioActual = (int) date('Y');
        $anios = [$anioActual, $anioActual + 1];

        $rows = $this->db->table('t_dinamicas')
            ->select('DISTINCT YEAR(fecha_inicio) AS anio', false)
            ->get()
            ->getResultArray();

        foreach ($rows as $r) {
            if (!empty($r['anio'])) {
                $anios[] = (int) $r['anio'];
            }
        }

        $anios = array_values(array_unique($anios));
        rsort($anios);
        return $anios;
    }
}
