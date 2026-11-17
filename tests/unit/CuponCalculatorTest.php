<?php

use App\Libraries\CuponCalculator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CuponCalculatorTest extends TestCase
{
    private CuponCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CuponCalculator();
    }

    public function testResolucionDeEstadosPorFechasYUsos(): void
    {
        $hoy = '2026-11-17';

        $this->assertSame(
            CuponCalculator::ESTADO_PROGRAMADO,
            $this->calculator->resolverEstado([
                'estado' => 'disponible',
                'fecha_inicio' => '2026-12-01',
                'fecha_vencimiento' => '2027-01-31',
                'limite_usos' => 1,
                'usos_realizados' => 0,
            ], $hoy)
        );

        $this->assertSame(
            CuponCalculator::ESTADO_DISPONIBLE,
            $this->calculator->resolverEstado([
                'estado' => 'disponible',
                'fecha_inicio' => '2026-11-01',
                'fecha_vencimiento' => '2027-01-31',
                'limite_usos' => 1,
                'usos_realizados' => 0,
            ], $hoy)
        );

        $this->assertSame(
            CuponCalculator::ESTADO_UTILIZADO,
            $this->calculator->resolverEstado([
                'estado' => 'disponible',
                'fecha_inicio' => '2026-11-01',
                'fecha_vencimiento' => '2027-01-31',
                'limite_usos' => 1,
                'usos_realizados' => 1,
            ], $hoy)
        );

        $this->assertSame(
            CuponCalculator::ESTADO_VENCIDO,
            $this->calculator->resolverEstado([
                'estado' => 'disponible',
                'fecha_inicio' => '2026-10-01',
                'fecha_vencimiento' => '2026-11-16',
                'limite_usos' => 1,
                'usos_realizados' => 0,
            ], $hoy)
        );

        $this->assertSame(
            CuponCalculator::ESTADO_CANCELADO,
            $this->calculator->resolverEstado([
                'estado' => 'cancelado',
                'fecha_inicio' => '2026-11-01',
                'fecha_vencimiento' => '2027-01-31',
                'limite_usos' => 1,
                'usos_realizados' => 0,
            ], $hoy)
        );
    }

    public function testEscenarioCompra150ConCuponFijo30(): void
    {
        $cupon = [
            'id' => 1,
            'id_cliente' => 10,
            'codigo' => 'NAV26-001',
            'tipo_descuento' => 'fijo',
            'valor' => 30.00,
            'compra_minima' => null,
            'fecha_inicio' => '2026-11-01',
            'fecha_vencimiento' => '2027-01-31',
            'limite_usos' => 1,
            'usos_realizados' => 0,
            'estado' => 'disponible',
        ];

        $res = $this->calculator->calcularDescuentoCupon($cupon, 150.00, 10, '2026-11-17');
        $this->assertTrue($res['valid']);
        $this->assertSame(30.00, $res['descuento']);
        $this->assertSame(120.00, $res['total_neto']);

        // Al incrementar uso, el cupón pasa automáticamente a utilizado y no se puede reutilizar
        $cupon['usos_realizados'] = 1;
        $reuso = $this->calculator->calcularDescuentoCupon($cupon, 150.00, 10, '2026-11-17');
        $this->assertFalse($reuso['valid']);
        $this->assertSame(CuponCalculator::ESTADO_UTILIZADO, $reuso['estado_resuelto']);
    }

    public function testRechazaCuponVencidoDeOtroClienteYUnSoloUsoMayorASubtotal(): void
    {
        $cuponBase = [
            'id_cliente' => 10,
            'codigo' => 'NAV26-001',
            'tipo_descuento' => 'fijo',
            'valor' => 30.00,
            'fecha_inicio' => '2026-11-01',
            'fecha_vencimiento' => '2026-11-10',
            'limite_usos' => 1,
            'usos_realizados' => 0,
            'estado' => 'disponible',
        ];

        // Otro cliente
        $otroCliente = $this->calculator->calcularDescuentoCupon($cuponBase, 150.00, 99, '2026-11-05');
        $this->assertFalse($otroCliente['valid']);

        // Vencido
        $vencido = $this->calculator->calcularDescuentoCupon($cuponBase, 150.00, 10, '2026-11-17');
        $this->assertFalse($vencido['valid']);
        $this->assertSame(CuponCalculator::ESTADO_VENCIDO, $vencido['estado_resuelto']);

        // Un solo uso cuyo importe ($30) supera subtotal ($20): se impide para conservar el beneficio
        $cuponVigente = $cuponBase;
        $cuponVigente['fecha_vencimiento'] = '2027-01-31';
        $mayorSubtotal = $this->calculator->calcularDescuentoCupon($cuponVigente, 20.00, 10, '2026-11-17');
        $this->assertFalse($mayorSubtotal['valid']);
    }

    public function testCuponPorcentajeConTopeMaximoYCompraMinima(): void
    {
        $cupon = [
            'id_cliente' => 5,
            'codigo' => 'FIDEL-20',
            'tipo_descuento' => 'porcentaje',
            'valor' => 20.00,
            'compra_minima' => 100.00,
            'descuento_maximo' => 50.00,
            'fecha_inicio' => '2026-11-01',
            'fecha_vencimiento' => null,
            'limite_usos' => 2,
            'usos_realizados' => 0,
            'estado' => 'disponible',
        ];

        // Debajo de compra mínima
        $noMinimo = $this->calculator->calcularDescuentoCupon($cupon, 80.00, 5, '2026-11-17');
        $this->assertFalse($noMinimo['valid']);

        // Aplica 20% de $200 = $40 (menor al tope de $50)
        $ok = $this->calculator->calcularDescuentoCupon($cupon, 200.00, 5, '2026-11-17');
        $this->assertTrue($ok['valid']);
        $this->assertSame(40.00, $ok['descuento']);
        $this->assertSame(160.00, $ok['total_neto']);

        // Aplica 20% de $400 = $80 -> topado a $50
        $topado = $this->calculator->calcularDescuentoCupon($cupon, 400.00, 5, '2026-11-17');
        $this->assertTrue($topado['valid']);
        $this->assertSame(50.00, $topado['descuento']);
        $this->assertSame(350.00, $topado['total_neto']);
    }

    public function testDescuentoManualRequiereMotivoYNoPermiteNetoNegativo(): void
    {
        $sinMotivo = $this->calculator->calcularDescuentoManual('fijo', 25.00, '   ', 100.00);
        $this->assertFalse($sinMotivo['valid']);

        $excedido = $this->calculator->calcularDescuentoManual('fijo', 120.00, 'Cortesía', 100.00);
        $this->assertFalse($excedido['valid']);

        $okFijo = $this->calculator->calcularDescuentoManual('fijo', 25.00, 'Compensación comercial', 100.00);
        $this->assertTrue($okFijo['valid']);
        $this->assertSame(25.00, $okFijo['descuento']);
        $this->assertSame(75.00, $okFijo['total_neto']);

        $okPct = $this->calculator->calcularDescuentoManual('porcentaje', 15.00, 'Cliente frecuente', 200.00);
        $this->assertTrue($okPct['valid']);
        $this->assertSame(30.00, $okPct['descuento']);
        $this->assertSame(170.00, $okPct['total_neto']);
    }

    public function testProrrateoExactoAlCentavoYResumenTarjeta(): void
    {
        $items = [
            ['importe_original' => 100.00],
            ['importe_original' => 50.00],
        ];
        $prorrateados = $this->calculator->prorratearDescuentoEnItems($items, 30.00);

        $this->assertSame(20.00, $prorrateados[0]['descuento_aplicado']);
        $this->assertSame(80.00, $prorrateados[0]['total_neto']);
        $this->assertSame(10.00, $prorrateados[1]['descuento_aplicado']);
        $this->assertSame(40.00, $prorrateados[1]['total_neto']);

        // Prorrateo con división no exacta (ej. $10.00 entre 3 ítems iguales de $33.33)
        $itemsTercios = [
            ['importe_original' => 33.33],
            ['importe_original' => 33.33],
            ['importe_original' => 33.34],
        ];
        $resTercios = $this->calculator->prorratearDescuentoEnItems($itemsTercios, 10.00);
        $sumaDesc = round(
            $resTercios[0]['descuento_aplicado']
            + $resTercios[1]['descuento_aplicado']
            + $resTercios[2]['descuento_aplicado'],
            2
        );
        $this->assertSame(10.00, $sumaDesc);

        // Resumen para la 5ta tarjeta
        $resumen = $this->calculator->resumirCuponesDisponibles([
            ['estado' => 'disponible', 'tipo_descuento' => 'fijo', 'valor' => 30.00, 'fecha_inicio' => '2026-11-01', 'fecha_vencimiento' => '2027-01-31', 'limite_usos' => 1, 'usos_realizados' => 0],
            ['estado' => 'disponible', 'tipo_descuento' => 'porcentaje', 'valor' => 15.00, 'fecha_inicio' => '2026-11-01', 'fecha_vencimiento' => '2027-01-31', 'limite_usos' => 1, 'usos_realizados' => 0],
            ['estado' => 'disponible', 'tipo_descuento' => 'fijo', 'valor' => 50.00, 'fecha_inicio' => '2026-11-01', 'fecha_vencimiento' => '2026-11-05', 'limite_usos' => 1, 'usos_realizados' => 0], // vencido
        ], '2026-11-17');

        $this->assertSame(30.00, $resumen['total_monetario_disponible']);
        $this->assertSame(2, $resumen['cantidad_activos']);
        $this->assertSame(1, $resumen['cantidad_porcentuales']);
    }
}

