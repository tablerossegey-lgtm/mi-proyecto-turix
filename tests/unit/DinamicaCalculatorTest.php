<?php

use App\Libraries\DinamicaCalculator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DinamicaCalculatorTest extends TestCase
{
    private DinamicaCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DinamicaCalculator();
    }

    private function dinamicaNavidad2026(): array
    {
        return [
            'id'                          => 1,
            'nombre'                      => 'Gran rifa navideña Turixshop 2026',
            'fecha_inicio'                => '2026-11-17',
            'fecha_fin'                   => '2026-12-14',
            'fecha_sorteo'                => '2026-12-16',
            'hora_sorteo'                 => '11:00:00',
            'tipo_requisito'              => 'acumulado_unico',
            'importe_minimo'              => 100.00,
            'max_participaciones_cliente' => 1,
            'max_premios_cliente'         => 1,
            'requiere_compra_liquidada'   => 0,
            'plantilla_whatsapp'          => "🎄🎉 ¡Ya estás participando en {dinamica}!\n👤 {cliente}\n📅 {fecha_sorteo}\n🕚 {hora_sorteo}",
        ];
    }

    public function testEscenariosAcumuladoMinimo100YExclusiones(): void
    {
        $dinamica = $this->dinamicaNavidad2026();

        $clientes = [
            1  => ['idCliente' => 1, 'nombre' => 'Cliente A', 'cel' => '9991112233'],
            2  => ['idCliente' => 2, 'nombre' => 'Cliente B', 'cel' => '9992223344'],
            3  => ['idCliente' => 3, 'nombre' => 'Cliente C', 'cel' => '9993334455'],
            4  => ['idCliente' => 4, 'nombre' => 'Cliente D', 'cel' => '9994445566'],
            5  => ['idCliente' => 5, 'nombre' => 'Cliente E', 'cel' => '9995556677'],
            63 => ['idCliente' => 63, 'nombre' => 'Noemí Carrillo', 'cel' => ''],
            399 => ['idCliente' => 399, 'nombre' => 'Pueblo Estadis', 'cel' => ''],
        ];

        $operaciones = [
            // Clientes 63 y 399 (excluidos): aunque compren >= $100 no deben participar
            ['id_registro' => 9, 'origen_tabla' => 'cuenta', 'idCliente' => 63, 'fecha' => '2026-11-17', 'monto' => 500.00, 'estatus' => '1', 'descripcion' => 'Compra interna'],
            ['id_registro' => 8, 'origen_tabla' => 'cuenta', 'idCliente' => 399, 'fecha' => '2026-11-19', 'monto' => 181.00, 'estatus' => '1', 'descripcion' => 'Teclado'],
            // Cliente A: compra de $115 a crédito (estatus 0) -> 1 participación
            ['id_registro' => 10, 'origen_tabla' => 'cuenta', 'idCliente' => 1, 'fecha' => '2026-11-17', 'monto' => 115.00, 'estatus' => '0', 'descripcion' => 'Termo'],
            // Cliente B: dos compras $60 y $40 -> 1 participación (cumple el 2026-11-25)
            ['id_registro' => 11, 'origen_tabla' => 'cuenta', 'idCliente' => 2, 'fecha' => '2026-11-18', 'monto' => 60.00, 'estatus' => '1', 'descripcion' => 'Taza'],
            ['id_registro' => 12, 'origen_tabla' => 'cuenta', 'idCliente' => 2, 'fecha' => '2026-11-25', 'monto' => 40.00, 'estatus' => '0', 'descripcion' => 'Llavero'],
            // Cliente C: compra de $350 + compra adicional $120 -> únicamente 1 participación
            ['id_registro' => 13, 'origen_tabla' => 'cuenta', 'idCliente' => 3, 'fecha' => '2026-11-20', 'monto' => 350.00, 'estatus' => '1', 'descripcion' => 'Frazada'],
            ['id_registro' => 14, 'origen_tabla' => 'cuenta', 'idCliente' => 3, 'fecha' => '2026-11-28', 'monto' => 120.00, 'estatus' => '1', 'descripcion' => 'Bolsa'],
            // Cliente D: compra de $80 + gasto de envío $50 + compra fuera de periodo $100 + compra cancelada $200 -> solo $80 válidos (próximo)
            ['id_registro' => 15, 'origen_tabla' => 'cuenta', 'idCliente' => 4, 'fecha' => '2026-11-22', 'monto' => 80.00, 'estatus' => '1', 'descripcion' => 'Vaso'],
            ['id_registro' => 16, 'origen_tabla' => 'cuenta', 'idCliente' => 4, 'fecha' => '2026-11-22', 'monto' => 50.00, 'estatus' => '1', 'descripcion' => 'Gasto de envío'],
            ['id_registro' => 17, 'origen_tabla' => 'cuenta', 'idCliente' => 4, 'fecha' => '2026-11-16', 'monto' => 100.00, 'estatus' => '1', 'descripcion' => 'Compra previa'],
            ['id_registro' => 18, 'origen_tabla' => 'cuenta', 'idCliente' => 4, 'fecha' => '2026-11-23', 'monto' => 200.00, 'estatus' => 'Cancelado', 'descripcion' => 'Pedido'],
            // Cliente E: compra $120 y luego devolución -$40 -> neto $80 -> pasa a próximos (no elegible)
            ['id_registro' => 19, 'origen_tabla' => 'cuenta', 'idCliente' => 5, 'fecha' => '2026-11-19', 'monto' => 120.00, 'estatus' => '1', 'descripcion' => 'Set'],
            ['id_registro' => 20, 'origen_tabla' => 'cuenta', 'idCliente' => 5, 'fecha' => '2026-11-21', 'monto' => -40.00, 'estatus' => '1', 'descripcion' => 'Devolución parcial'],
        ];

        $res = $this->calculator->evaluarClientesPeriodo($operaciones, $clientes, $dinamica);

        $this->assertArrayHasKey(1, $res['elegibles']);
        $this->assertArrayHasKey(2, $res['elegibles']);
        $this->assertArrayHasKey(3, $res['elegibles']);
        $this->assertArrayNotHasKey(4, $res['elegibles']);
        $this->assertArrayNotHasKey(5, $res['elegibles']);
        $this->assertArrayNotHasKey(63, $res['elegibles']);
        $this->assertArrayNotHasKey(399, $res['elegibles']);

        $this->assertSame(1, $res['elegibles'][1]['num_participaciones']);
        $this->assertSame('2026-11-17', $res['elegibles'][1]['fecha_cumplimiento']);

        $this->assertSame(1, $res['elegibles'][2]['num_participaciones']);
        $this->assertSame(100.0, $res['elegibles'][2]['importe_acumulado']);
        $this->assertSame('2026-11-25', $res['elegibles'][2]['fecha_cumplimiento']);

        $this->assertSame(1, $res['elegibles'][3]['num_participaciones']);
        $this->assertSame(470.0, $res['elegibles'][3]['importe_acumulado']);

        $this->assertCount(2, $res['proximos']);
        $this->assertSame(80.0, $res['proximos'][0]['importe_acumulado']);
        $this->assertSame(20.0, $res['proximos'][0]['importe_faltante']);
        $this->assertSame(80.0, $res['proximos'][0]['porcentaje_avance']);
    }

    public function testExclusionDeReferenciasManualesYCompraLiquidada(): void
    {
        $dinamica = $this->dinamicaNavidad2026();
        $dinamica['requiere_compra_liquidada'] = 1;

        $clientes = [
            1 => ['idCliente' => 1, 'nombre' => 'Cliente Crédito', 'cel' => ''],
            2 => ['idCliente' => 2, 'nombre' => 'Cliente Manual Ref', 'cel' => ''],
        ];

        $operaciones = [
            ['id_registro' => 100, 'folio_compra' => 'FOL-100', 'origen_tabla' => 'cuenta', 'idCliente' => 1, 'fecha' => '2026-11-20', 'monto' => 150.00, 'estatus' => '0', 'descripcion' => 'Crédito pendiente'],
            ['id_registro' => 101, 'folio_compra' => 'FOL-101', 'origen_tabla' => 'cuenta', 'idCliente' => 2, 'fecha' => '2026-11-20', 'monto' => 150.00, 'estatus' => '1', 'descripcion' => 'Ya capturada manual'],
        ];

        $referenciasManuales = ['FOL-101' => true];

        $res = $this->calculator->evaluarClientesPeriodo($operaciones, $clientes, $dinamica, $referenciasManuales);

        $this->assertEmpty($res['elegibles']);
        $this->assertEmpty($res['proximos']);
    }

    public function testListadoAppSorteosDesambiguacionYRestantes(): void
    {
        $dinamica = $this->dinamicaNavidad2026();
        $participantes = [
            ['id' => 1, 'id_cliente' => 10, 'numero_control' => 1, 'nombre_participante' => 'María López', 'num_participaciones' => 1, 'estado' => 'elegible'],
            ['id' => 2, 'id_cliente' => 11, 'numero_control' => 2, 'nombre_participante' => 'María López', 'num_participaciones' => 1, 'estado' => 'elegible'],
            ['id' => 3, 'id_cliente' => 12, 'numero_control' => 3, 'nombre_participante' => 'Juan Pérez', 'num_participaciones' => 2, 'estado' => 'elegible'],
            ['id' => 4, 'id_cliente' => 13, 'numero_control' => 4, 'nombre_participante' => 'No Elegible', 'num_participaciones' => 0, 'estado' => 'no_elegible'],
        ];

        $lineas = $this->calculator->generarListadoSorteo($participantes, $dinamica);
        $this->assertSame([
            'María López [#001]',
            'María López [#002]',
            'Juan Pérez (1/2)',
            'Juan Pérez (2/2)',
        ], $lineas);

        // Excluyendo al cliente 10 (ya ganó un premio)
        $restantes = $this->calculator->generarListadoSorteo($participantes, $dinamica, [10], [1]);
        $this->assertSame([
            'María López',
            'Juan Pérez (1/2)',
            'Juan Pérez (2/2)',
        ], $restantes);
    }

    public function testMensajeWhatsAppConVariables(): void
    {
        $dinamica = $this->dinamicaNavidad2026();
        $part = ['nombre_participante' => 'Ana García', 'num_participaciones' => 1];

        $msg = $this->calculator->generarMensajeWhatsApp($dinamica, $part);

        $this->assertStringContainsString('Gran rifa navideña Turixshop 2026', $msg);
        $this->assertStringContainsString('Ana García', $msg);
        $this->assertStringContainsString('Miércoles 16 de diciembre', $msg);
        $this->assertStringContainsString('11:00 a. m.', $msg);
    }
}
