<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaSemillaModel extends Model
{
    protected $table            = 't_ventas_semillas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cliente',
        'nombre_cliente',
        'fecha',
        'producto',
        'cantidad',
        'precio_venta',
        'precio_bea',
        'estatus_pago',
        'entregado_bea',
        'id_cuenta_cliente'
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Obtiene todas las ventas con información de clientes si existe id_cliente
     */
    public function obtenerVentasConClientes()
    {
        return $this->select('t_ventas_semillas.*, t_clientes.nombre as cliente_registrado, t_clientes.cel as cel_cliente')
                    ->join('t_clientes', 't_clientes.idCliente = t_ventas_semillas.id_cliente', 'left')
                    ->orderBy('t_ventas_semillas.fecha', 'DESC')
                    ->orderBy('t_ventas_semillas.id', 'DESC')
                    ->findAll();
    }

    /**
     * Calcula las estadísticas financieras para Bea y el usuario
     */
    public function obtenerEstadisticas()
    {
        $db = \Config\Database::connect();
        
        // 1. Pendiente por cobrar a clientes (estatus_pago = 'Pendiente')
        $pendienteCobrar = $this->selectSum('precio_bea', 'total')
                                ->where('estatus_pago', 'Pendiente')
                                ->get()
                                ->getRow()
                                ->total ?? 0.00;

        // Note: selectSum sums price_bea directly, but we need (precio_bea * cantidad).
        // Let's calculate mathematically with select('SUM(precio_bea * cantidad) as total') for accuracy:
        $queryPendiente = $db->query("SELECT SUM(precio_bea * cantidad) as total FROM t_ventas_semillas WHERE estatus_pago = 'Pendiente'");
        $pendienteCobrar = (float)($queryPendiente->getRow()->total ?? 0.00);

        // 2. Cobrado, listo para entregar a Bea (estatus_pago = 'Pagado' AND entregado_bea = 'No')
        $queryPorEntregar = $db->query("SELECT SUM(precio_bea * cantidad) as total FROM t_ventas_semillas WHERE estatus_pago = 'Pagado' AND entregado_bea = 'No'");
        $porEntregarBea = (float)($queryPorEntregar->getRow()->total ?? 0.00);

        // 3. Ya entregado a Bea (entregado_bea = 'Si')
        $queryEntregado = $db->query("SELECT SUM(precio_bea * cantidad) as total FROM t_ventas_semillas WHERE entregado_bea = 'Si'");
        $entregadoBea = (float)($queryEntregado->getRow()->total ?? 0.00);

        // 4. Ganancia del usuario (estatus_pago = 'Pagado', ganancia = (precio_venta - precio_bea) * cantidad)
        $queryGanancia = $db->query("SELECT SUM((precio_venta - precio_bea) * cantidad) as total FROM t_ventas_semillas WHERE estatus_pago = 'Pagado'");
        $gananciaTotal = (float)($queryGanancia->getRow()->total ?? 0.00);

        return [
            'pendiente_cobrar' => $pendienteCobrar,
            'por_entregar_bea' => $porEntregarBea,
            'entregado_bea'    => $entregadoBea,
            'ganancia_total'   => $gananciaTotal
        ];
    }

    /**
     * Obtiene el listado de ventas agrupado por cliente
     */
    public function obtenerVentasPorClientes()
    {
        $db = \Config\Database::connect();
        
        // Consultar ventas agrupando por cliente
        // Primero, los clientes registrados en t_clientes
        $sql = "SELECT 
                    COALESCE(c.idCliente, 0) as id_cliente,
                    COALESCE(c.nombre, v.nombre_cliente, 'Cliente General/Desconocido') as nombre_cliente,
                    c.cel as cel,
                    COUNT(v.id) as total_ventas,
                    SUM(v.cantidad) as total_productos,
                    SUM(v.precio_venta * v.cantidad) as total_monto,
                    SUM(CASE WHEN v.estatus_pago = 'Pendiente' THEN (v.precio_venta * v.cantidad) ELSE 0 END) as total_pendiente
                FROM t_ventas_semillas v
                LEFT JOIN t_clientes c ON c.idCliente = v.id_cliente
                GROUP BY COALESCE(c.idCliente, 0), COALESCE(c.nombre, v.nombre_cliente)
                ORDER BY total_pendiente DESC, nombre_cliente ASC";
                
        return $db->query($sql)->getResultArray();
    }
}
