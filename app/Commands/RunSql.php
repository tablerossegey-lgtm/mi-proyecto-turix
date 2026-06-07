<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunSql extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:run-sql';
    protected $description = 'Runs the SQL to alter table and add id_producto';
    protected $usage       = 'php spark app:run-sql';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write("Running ALTER TABLE on t_compras_proveedor_detalle...", "yellow");
        
        try {
            $db->query("ALTER TABLE t_compras_proveedor_detalle 
                ADD COLUMN id_producto INT NULL AFTER idCompraProveedor,
                ADD CONSTRAINT fk_detalle_compra_producto FOREIGN KEY (id_producto) REFERENCES t_inventario(id) ON DELETE SET NULL");
            CLI::write("Success! Column id_producto and Foreign Key added.", "green");
        } catch (\Exception $e) {
            CLI::error("Failed: " . $e->getMessage());
        }
    }
}

