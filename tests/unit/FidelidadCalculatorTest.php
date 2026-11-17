<?php

use App\Libraries\FidelidadCalculator;
use App\Libraries\XlsxExporter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FidelidadCalculatorTest extends TestCase
{
    private FidelidadCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FidelidadCalculator();
    }

    public function testPeriodoSinComprasNoDivideEntreCero(): void
    {
        $periodo = $this->calculator->resolverPeriodo('anio', 2025, null, null, '2026-11-17');
        $res = $this->calculator->calcularRanking([], [1 => ['idCliente' => 1, 'nombre' => 'Ana']], $periodo);

        $this->assertSame([], $res['ranking']);
        $this->assertSame(0, $res['resumen']['total_clientes_activos']);
        $this->assertSame(0, $res['resumen']['total_compras']);
        $this->assertSame(0.0, $res['resumen']['importe_total']);
        $this->assertSame(0.0, $res['resumen']['promedio_compras']);
        $this->assertNull($res['resumen']['mejor_cliente']);
    }

    public function testReglasDeNegocioExclusionesYDevoluciones(): void
    {
        $periodo = $this->calculator->resolverPeriodo('personalizado', null, '2026-01-01', '2026-06-30', '2026-06-30');
        $clientes = [
            1 => ['idCliente' => 1, 'nombre' => 'Ana Frecuente'],
            2 => ['idCliente' => 2, 'nombre' => 'Luis Unica'],
            3 => ['idCliente' => 3, 'nombre' => 'Solo Devolucion'],
        ];

        $operaciones = [
            // Ana: 2 productos el mismo día (1 compra), 1 paquete de pilas (NO envío), 1 gasto de envío (excluido), 1 devolución (-50), 2 compras más en distintos meses
            ['idCliente' => 1, 'fecha' => '2026-01-10', 'monto' => 100.00, 'estatus' => '1', 'descripcion' => 'Taza'],
            ['idCliente' => 1, 'fecha' => '2026-01-10', 'monto' => 150.00, 'estatus' => '1', 'descripcion' => 'Paquete de pilas AA'],
            ['idCliente' => 1, 'fecha' => '2026-01-10', 'monto' => 90.00, 'estatus' => '1', 'descripcion' => 'Gasto de envío'],
            ['idCliente' => 1, 'fecha' => '2026-02-15', 'monto' => 200.00, 'estatus' => '0', 'descripcion' => 'Encargo lámpara'],
            ['idCliente' => 1, 'fecha' => '2026-02-20', 'monto' => -50.00, 'estatus' => '1', 'descripcion' => 'Devolución parcial'],
            ['idCliente' => 1, 'fecha' => '2026-06-20', 'monto' => 100.00, 'estatus' => '1', 'descripcion' => 'Termo'],
            // Compra cancelada de Ana debe excluirse
            ['idCliente' => 1, 'fecha' => '2026-03-05', 'monto' => 500.00, 'estatus' => 'Cancelado', 'descripcion' => 'Pedido'],

            // Luis: 1 sola compra grande en enero (mayor importe pero baja frecuencia, constancia y recencia)
            ['idCliente' => 2, 'fecha' => '2026-01-05', 'monto' => 1000.00, 'estatus' => '1', 'descripcion' => 'Lote artículos'],

            // Cliente 3: solo una devolución y un envío -> 0 compras válidas -> excluido
            ['idCliente' => 3, 'fecha' => '2026-04-01', 'monto' => -120.00, 'estatus' => '1', 'descripcion' => 'Devolución'],
            ['idCliente' => 3, 'fecha' => '2026-04-02', 'monto' => 80.00, 'estatus' => '1', 'descripcion' => 'Paquetería'],
        ];

        $res = $this->calculator->calcularRanking($operaciones, $clientes, $periodo);
        $ranking = $res['ranking'];

        $this->assertCount(2, $ranking);
        $this->assertSame(1, $ranking[0]['idCliente']);
        $this->assertSame(1, $ranking[0]['posicion']);
        // Ana: 3 compras (ene 10, feb 15, jun 20), 3 meses activos, importe 250 + 200 - 50 + 100 = 500
        $this->assertSame(3, $ranking[0]['numero_compras']);
        $this->assertSame(3, $ranking[0]['meses_activos']);
        $this->assertSame(500.0, $ranking[0]['importe_total']);
        $this->assertSame(40.0, $ranking[0]['puntaje_frecuencia']);
        $this->assertSame(15.0, $ranking[0]['puntaje_constancia']); // 3 / 6 meses * 30
        $this->assertSame(10.0, $ranking[0]['puntaje_importe']); // 500 / 1000 * 20
        $this->assertSame(10.0, $ranking[0]['puntaje_recencia']); // 10 días <= 30
        $this->assertSame(75.0, $ranking[0]['puntaje_total']);

        // Luis: 1 compra, 1 mes, importe 1000, última compra 2026-01-05 (176 días -> 2.5 pts)
        $this->assertSame(2, $ranking[1]['idCliente']);
        $this->assertSame(2.5, $ranking[1]['puntaje_recencia']);
    }

    public function testMultiplesComprasEnMismoMesYDesempate(): void
    {
        $periodo = $this->calculator->resolverPeriodo('personalizado', null, '2026-01-01', '2026-03-31', '2026-03-31');
        $clientes = [
            10 => ['idCliente' => 10, 'nombre' => 'Cliente A'],
            20 => ['idCliente' => 20, 'nombre' => 'Cliente B'],
        ];

        // Ambos compran 2 veces en el mismo mes (marzo) con el mismo importe y misma fecha última
        $operaciones = [
            ['idCliente' => 20, 'fecha' => '2026-03-01', 'monto' => 100.0, 'estatus' => '1', 'descripcion' => 'Prod 1'],
            ['idCliente' => 20, 'fecha' => '2026-03-20', 'monto' => 100.0, 'estatus' => '1', 'descripcion' => 'Prod 2'],
            ['idCliente' => 10, 'fecha' => '2026-03-01', 'monto' => 100.0, 'estatus' => '1', 'descripcion' => 'Prod 1'],
            ['idCliente' => 10, 'fecha' => '2026-03-20', 'monto' => 100.0, 'estatus' => '1', 'descripcion' => 'Prod 2'],
        ];

        $res = $this->calculator->calcularRanking($operaciones, $clientes, $periodo);
        $ranking = $res['ranking'];

        $this->assertSame(2, $ranking[0]['numero_compras']);
        $this->assertSame(1, $ranking[0]['meses_activos']);
        // Empate total -> desempate final por idCliente ASC (10 antes que 20)
        $this->assertSame(10, $ranking[0]['idCliente']);
        $this->assertSame(20, $ranking[1]['idCliente']);
    }

    public function testPuntajesRecencia(): void
    {
        $this->assertSame(10.0, $this->calculator->calcularPuntajeRecencia(0));
        $this->assertSame(10.0, $this->calculator->calcularPuntajeRecencia(30));
        $this->assertSame(7.5, $this->calculator->calcularPuntajeRecencia(31));
        $this->assertSame(7.5, $this->calculator->calcularPuntajeRecencia(60));
        $this->assertSame(5.0, $this->calculator->calcularPuntajeRecencia(61));
        $this->assertSame(5.0, $this->calculator->calcularPuntajeRecencia(90));
        $this->assertSame(2.5, $this->calculator->calcularPuntajeRecencia(91));
        $this->assertSame(2.5, $this->calculator->calcularPuntajeRecencia(180));
        $this->assertSame(0.0, $this->calculator->calcularPuntajeRecencia(181));
    }

    public function testGeneradorXlsxProduceArchivoValido(): void
    {
        $exporter = new XlsxExporter();
        $bin = $exporter->generarBinario('Ranking', [
            [['v' => 'Prueba TurixShop', 's' => 1]],
            [1, 'Ana', 3, 2, ['v' => 500.5, 's' => 3]],
        ], [10, 20, 10, 10, 15]);

        $this->assertStringStartsWith('PK', $bin);
        $this->assertGreaterThan(500, strlen($bin));
    }

    public function testExcluyeClienteAdministradoraId63DeRankingYMaximos(): void
    {
        $periodo = $this->calculator->resolverPeriodo('personalizado', null, '2026-01-01', '2026-03-31', '2026-03-31');
        $clientes = [
            63 => ['idCliente' => 63, 'nombre' => 'Noemí Carrillo'],
            1 => ['idCliente' => 1, 'nombre' => 'Cliente Real'],
        ];

        $operaciones = [
            // Id 63 tiene muchas compras e importe alto, pero debe quedar excluido completamente
            ['idCliente' => 63, 'fecha' => '2026-01-05', 'monto' => 5000.0, 'estatus' => '1', 'descripcion' => 'Interno 1'],
            ['idCliente' => 63, 'fecha' => '2026-02-05', 'monto' => 5000.0, 'estatus' => '1', 'descripcion' => 'Interno 2'],
            ['idCliente' => 63, 'fecha' => '2026-03-05', 'monto' => 5000.0, 'estatus' => '1', 'descripcion' => 'Interno 3'],
            // Cliente real con 1 compra debe obtener los 40 pts de frecuencia y 20 pts de importe
            ['idCliente' => 1, 'fecha' => '2026-03-10', 'monto' => 250.0, 'estatus' => '1', 'descripcion' => 'Producto'],
        ];

        $res = $this->calculator->calcularRanking($operaciones, $clientes, $periodo);

        $this->assertCount(1, $res['ranking']);
        $this->assertSame(1, $res['ranking'][0]['idCliente']);
        $this->assertSame(40.0, $res['ranking'][0]['puntaje_frecuencia']);
        $this->assertSame(20.0, $res['ranking'][0]['puntaje_importe']);
        $this->assertSame(1, $res['resumen']['total_clientes_activos']);
        $this->assertSame(250.0, $res['resumen']['importe_total']);
    }
}
