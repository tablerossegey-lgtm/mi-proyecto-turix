<?php

namespace App\Libraries;

/**
 * Calculadora y validador de reglas de negocio para cupones y descuentos.
 */
class CuponCalculator
{
    public const ESTADO_PROGRAMADO = 'programado';
    public const ESTADO_DISPONIBLE = 'disponible';
    public const ESTADO_UTILIZADO  = 'utilizado';
    public const ESTADO_VENCIDO    = 'vencido';
    public const ESTADO_CANCELADO  = 'cancelado';

    /**
     * Resuelve el estado real de un cupón considerando cancelación, usos y fechas de vigencia.
     */
    public function resolverEstado(array $cupon, ?string $hoy = null): string
    {
        $hoy = $hoy ?: date('Y-m-d');

        if (($cupon['estado'] ?? '') === self::ESTADO_CANCELADO) {
            return self::ESTADO_CANCELADO;
        }

        $limiteUsos     = max(1, (int)($cupon['limite_usos'] ?? 1));
        $usosRealizados = max(0, (int)($cupon['usos_realizados'] ?? 0));
        if ($usosRealizados >= $limiteUsos) {
            return self::ESTADO_UTILIZADO;
        }

        $fechaVencimiento = $cupon['fecha_vencimiento'] ?? null;
        if (!empty($fechaVencimiento) && $hoy > $fechaVencimiento) {
            return self::ESTADO_VENCIDO;
        }

        $fechaInicio = $cupon['fecha_inicio'] ?? null;
        if (!empty($fechaInicio) && $hoy < $fechaInicio) {
            return self::ESTADO_PROGRAMADO;
        }

        return self::ESTADO_DISPONIBLE;
    }

    /**
     * Valida y calcula el descuento de un cupón sobre un subtotal elegible (sin gastos de envío).
     *
     * @return array{valid:bool, message?:string, descuento?:float, subtotal_elegible?:float, total_neto?:float, estado_resuelto?:string}
     */
    public function calcularDescuentoCupon(array $cupon, float $subtotalElegible, int $idCliente, ?string $hoy = null): array
    {
        if ((int)($cupon['id_cliente'] ?? 0) !== $idCliente) {
            return [
                'valid'   => false,
                'message' => 'El cupón seleccionado pertenece a otro cliente y no puede aplicarse a esta cuenta.',
            ];
        }

        $estado = $this->resolverEstado($cupon, $hoy);
        if ($estado !== self::ESTADO_DISPONIBLE) {
            $mensajes = [
                self::ESTADO_CANCELADO  => 'El cupón fue cancelado y ya no está disponible.',
                self::ESTADO_UTILIZADO  => 'El cupón ya alcanzó su límite de usos disponibles.',
                self::ESTADO_VENCIDO    => 'El cupón se encuentra vencido.',
                self::ESTADO_PROGRAMADO => 'El cupón aún no inicia su periodo de vigencia.',
            ];
            return [
                'valid'           => false,
                'estado_resuelto' => $estado,
                'message'         => $mensajes[$estado] ?? 'El cupón no está disponible.',
            ];
        }

        $subtotalElegible = round(max(0.0, $subtotalElegible), 2);
        if ($subtotalElegible <= 0) {
            return [
                'valid'   => false,
                'message' => 'El subtotal elegible de productos debe ser mayor a $0.00 para aplicar un cupón.',
            ];
        }

        $compraMinima = isset($cupon['compra_minima']) && $cupon['compra_minima'] !== '' && $cupon['compra_minima'] !== null
            ? round((float)$cupon['compra_minima'], 2)
            : 0.0;

        if ($compraMinima > 0 && $subtotalElegible < $compraMinima) {
            return [
                'valid'   => false,
                'message' => 'La compra no alcanza el importe mínimo requerido de $' . number_format($compraMinima, 2) . ' para este cupón.',
            ];
        }

        $tipo       = $cupon['tipo_descuento'] ?? 'fijo';
        $valor      = round((float)($cupon['valor'] ?? 0), 2);
        $limiteUsos = max(1, (int)($cupon['limite_usos'] ?? 1));

        if ($valor <= 0) {
            return [
                'valid'   => false,
                'message' => 'El valor del cupón debe ser mayor a cero.',
            ];
        }

        if ($tipo === 'fijo') {
            if ($limiteUsos === 1 && $valor > $subtotalElegible) {
                return [
                    'valid'   => false,
                    'message' => 'El valor del cupón ($' . number_format($valor, 2) . ') supera el subtotal elegible ($' . number_format($subtotalElegible, 2) . '). Conserva este cupón de un solo uso para una compra mayor.',
                ];
            }
            $descuento = min($valor, $subtotalElegible);
        } elseif ($tipo === 'porcentaje') {
            if ($valor > 100) {
                return [
                    'valid'   => false,
                    'message' => 'El porcentaje de descuento no puede superar el 100%.',
                ];
            }
            $descuento = round($subtotalElegible * ($valor / 100), 2);

            $descuentoMaximo = isset($cupon['descuento_maximo']) && $cupon['descuento_maximo'] !== '' && $cupon['descuento_maximo'] !== null
                ? round((float)$cupon['descuento_maximo'], 2)
                : 0.0;

            if ($descuentoMaximo > 0) {
                $descuento = min($descuento, $descuentoMaximo);
            }
            $descuento = min($descuento, $subtotalElegible);
        } else {
            return [
                'valid'   => false,
                'message' => 'Tipo de descuento de cupón no válido.',
            ];
        }

        $descuento = round(max(0.0, $descuento), 2);
        $totalNeto = round(max(0.0, $subtotalElegible - $descuento), 2);

        return [
            'valid'             => true,
            'estado_resuelto'   => $estado,
            'descuento'         => $descuento,
            'subtotal_elegible' => $subtotalElegible,
            'total_neto'        => $totalNeto,
        ];
    }

    /**
     * Valida y calcula un descuento manual (importe fijo o porcentaje) sobre el subtotal elegible.
     *
     * @return array{valid:bool, message?:string, descuento?:float, total_neto?:float}
     */
    public function calcularDescuentoManual(string $tipo, float $valor, string $motivo, float $subtotalElegible): array
    {
        if (trim($motivo) === '') {
            return [
                'valid'   => false,
                'message' => 'Debes especificar obligatoriamente el motivo del descuento manual.',
            ];
        }

        $subtotalElegible = round(max(0.0, $subtotalElegible), 2);
        $valor            = round($valor, 2);

        if ($subtotalElegible <= 0) {
            return [
                'valid'   => false,
                'message' => 'El subtotal de productos debe ser mayor a $0.00.',
            ];
        }

        if ($valor <= 0) {
            return [
                'valid'   => false,
                'message' => 'El valor del descuento manual debe ser mayor a 0.',
            ];
        }

        if ($tipo === 'fijo') {
            if ($valor > $subtotalElegible) {
                return [
                    'valid'   => false,
                    'message' => 'El descuento manual no puede ser mayor al subtotal elegible ($' . number_format($subtotalElegible, 2) . ').',
                ];
            }
            $descuento = $valor;
        } elseif ($tipo === 'porcentaje') {
            if ($valor > 100) {
                return [
                    'valid'   => false,
                    'message' => 'El porcentaje de descuento manual no puede exceder 100%.',
                ];
            }
            $descuento = round($subtotalElegible * ($valor / 100), 2);
        } else {
            return [
                'valid'   => false,
                'message' => 'Tipo de descuento manual no válido.',
            ];
        }

        $totalNeto = round(max(0.0, $subtotalElegible - $descuento), 2);

        return [
            'valid'      => true,
            'descuento'  => round($descuento, 2),
            'total_neto' => $totalNeto,
        ];
    }

    /**
     * Prorratea un descuento total proporcionalmente entre los ítems elegibles,
     * garantizando exactitud al centavo y que ningún ítem quede con importe negativo.
     *
     * @param array<int, array<string, mixed>> $items Cada ítem debe incluir 'importe_original'
     * @return array<int, array<string, mixed>>
     */
    public function prorratearDescuentoEnItems(array $items, float $descuentoTotal): array
    {
        $descuentoTotal = round(max(0.0, $descuentoTotal), 2);
        $totalBase      = 0.0;
        $lastEligible   = null;

        foreach ($items as $idx => $item) {
            $orig = round((float)($item['importe_original'] ?? 0), 2);
            if ($orig > 0) {
                $totalBase   += $orig;
                $lastEligible = $idx;
            }
        }

        if ($descuentoTotal <= 0 || $totalBase <= 0 || $lastEligible === null) {
            foreach ($items as $idx => $item) {
                $orig = round((float)($item['importe_original'] ?? 0), 2);
                $items[$idx]['descuento_aplicado'] = 0.00;
                $items[$idx]['total_neto']         = $orig;
            }
            return $items;
        }

        $descuentoAplicadoAcum = 0.0;

        foreach ($items as $idx => $item) {
            $orig = round((float)($item['importe_original'] ?? 0), 2);
            if ($orig <= 0) {
                $items[$idx]['descuento_aplicado'] = 0.00;
                $items[$idx]['total_neto']         = 0.00;
                continue;
            }

            if ($idx === $lastEligible) {
                $descItem = round($descuentoTotal - $descuentoAplicadoAcum, 2);
            } else {
                $descItem = round($descuentoTotal * ($orig / $totalBase), 2);
            }

            // Salvaguarda para nunca producir total negativo
            $descItem = min($descItem, $orig);
            $descItem = max(0.0, $descItem);

            $descuentoAplicadoAcum = round($descuentoAplicadoAcum + $descItem, 2);

            $items[$idx]['descuento_aplicado'] = $descItem;
            $items[$idx]['total_neto']         = round($orig - $descItem, 2);
        }

        return $items;
    }

    /**
     * Genera el resumen de cupones disponibles para la tarjeta informativa del cliente.
     */
    public function resumirCuponesDisponibles(array $cupones, ?string $hoy = null): array
    {
        $totalMonetarioDisponible = 0.0;
        $cantidadActivos          = 0;
        $cantidadPorcentuales     = 0;

        foreach ($cupones as $cupon) {
            $estado = $this->resolverEstado($cupon, $hoy);
            if ($estado === self::ESTADO_DISPONIBLE) {
                $cantidadActivos++;
                if (($cupon['tipo_descuento'] ?? '') === 'fijo') {
                    $totalMonetarioDisponible += (float)($cupon['valor'] ?? 0);
                } elseif (($cupon['tipo_descuento'] ?? '') === 'porcentaje') {
                    $cantidadPorcentuales++;
                }
            }
        }

        return [
            'total_monetario_disponible' => round($totalMonetarioDisponible, 2),
            'cantidad_activos'           => $cantidadActivos,
            'cantidad_porcentuales'      => $cantidadPorcentuales,
        ];
    }

    /**
     * Calcula el nuevo código único con formato TUR-XXXXXX (o prefijo personalizado).
     */
    public function generarCodigoAleatorio(string $prefijo = 'CUP'): string
    {
        $prefijo = preg_replace('/[^A-Z0-9]/', '', strtoupper($prefijo)) ?: 'CUP';
        $chars   = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $rand    = '';
        for ($i = 0; $i < 6; $i++) {
            $rand .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $prefijo . '-' . $rand;
    }
}

