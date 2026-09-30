<?php

namespace App\Models;

use CodeIgniter\Model;

class FondoAportacionModel extends Model
{
    protected $table            = 't_fondos_aportaciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_fondo',
        'anio',
        'mes',
        'balance_caja',
        'porcentaje_aplicado',
        'monto_sugerido',
        'monto_aportado',
        'tipo_aportacion',
        'fecha_registro',
        'notas'
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Obtener el total acumulado de un fondo
     */
    public function getTotalAcumulado(int $idFondo): float
    {
        $builder = $this->builder();
        $builder->selectSum('monto_aportado', 'total');
        $builder->where('id_fondo', $idFondo);
        $result = $builder->get()->getRowArray();
        return (float)($result['total'] ?? 0);
    }

    /**
     * Obtener la última aportación registrada para un fondo
     */
    public function getUltimaAportacion(int $idFondo): ?array
    {
        return $this->where('id_fondo', $idFondo)
                    ->orderBy('fecha_registro', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Verificar si ya existe una aportación para ese mes y año
     */
    public function existeAportacionMes(int $idFondo, int $anio, int $mes, string $tipo = 'Mensual'): bool
    {
        return $this->where('id_fondo', $idFondo)
                    ->where('anio', $anio)
                    ->where('mes', $mes)
                    ->where('tipo_aportacion', $tipo)
                    ->countAllResults() > 0;
    }

    /**
     * Obtener el historial completo con cálculo de saldo acumulado histórico
     */
    public function getHistorial(int $idFondo): array
    {
        $aportaciones = $this->where('id_fondo', $idFondo)
                             ->orderBy('anio', 'ASC')
                             ->orderBy('mes', 'ASC')
                             ->orderBy('id', 'ASC')
                             ->findAll();

        $acumulado = 0.00;
        foreach ($aportaciones as &$ap) {
            $acumulado += (float)$ap['monto_aportado'];
            $ap['saldo_acumulado'] = $acumulado;
        }

        // Devolvemos en orden descendente (lo más reciente primero)
        return array_reverse($aportaciones);
    }
}
