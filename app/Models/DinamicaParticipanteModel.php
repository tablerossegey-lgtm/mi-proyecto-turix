<?php

namespace App\Models;

use CodeIgniter\Model;

class DinamicaParticipanteModel extends Model
{
    protected $table            = 't_dinamica_participantes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_dinamica',
        'id_cliente',
        'es_ocasional',
        'numero_control',
        'nombre_participante',
        'telefono',
        'importe_acumulado',
        'num_compras_validas',
        'num_participaciones',
        'fecha_cumplimiento',
        'origen',
        'estado',
        'motivo_estado',
        'referencia_compra',
        'motivo_registro',
        'observaciones',
        'notificado_whatsapp',
        'fecha_notificacion',
        'notificado_por_nombre',
        'en_listado_definitivo',
        'detalle_compras_json',
    ];

    protected $useTimestamps = false;

    public function obtenerSiguienteNumeroControl(int $idDinamica): int
    {
        $row = $this->db->table($this->table)
            ->selectMax('numero_control', 'max_num')
            ->where('id_dinamica', $idDinamica)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_num'] ?? 0)) + 1;
    }

    public function obtenerPorDinamica(int $idDinamica, array $filtros = []): array
    {
        $builder = $this->where('id_dinamica', $idDinamica)
            ->orderBy('numero_control', 'ASC');

        if (!empty($filtros['q'])) {
            $builder->like('nombre_participante', trim((string) $filtros['q']));
        }
        if (!empty($filtros['estado'])) {
            $builder->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['origen'])) {
            $builder->where('origen', $filtros['origen']);
        }
        if (isset($filtros['notificado']) && $filtros['notificado'] !== '') {
            $builder->where('notificado_whatsapp', (int) $filtros['notificado']);
        }

        return $builder->findAll();
    }

    public function obtenerReferenciasManualesMapa(int $idDinamica): array
    {
        $rows = $this->select('referencia_compra')
            ->where('id_dinamica', $idDinamica)
            ->where('origen', 'manual')
            ->where('referencia_compra IS NOT NULL')
            ->where('referencia_compra !=', '')
            ->findAll();

        $mapa = [];
        foreach ($rows as $r) {
            $ref = trim((string) $r['referencia_compra']);
            if ($ref !== '') {
                $mapa[$ref] = true;
            }
        }
        return $mapa;
    }
}
