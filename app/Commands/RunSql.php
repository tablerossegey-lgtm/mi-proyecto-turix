<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunSql extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:run-sql';
    protected $description = 'Runs maintenance SQL migrations for application schema';
    protected $usage       = 'php spark app:run-sql';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        CLI::write("Checking and adding promo dates to t_inventario...", "yellow");
        
        try {
            $fields = $db->getFieldNames('t_inventario');
            if (!in_array('fecha_inicio_promo', $fields)) {
                $db->query("ALTER TABLE t_inventario ADD COLUMN fecha_inicio_promo DATE NULL DEFAULT NULL AFTER precio_promo");
                CLI::write("Column fecha_inicio_promo added.", "green");
            } else {
                CLI::write("Column fecha_inicio_promo already exists.", "blue");
            }

            if (!in_array('fecha_fin_promo', $fields)) {
                $db->query("ALTER TABLE t_inventario ADD COLUMN fecha_fin_promo DATE NULL DEFAULT NULL AFTER fecha_inicio_promo");
                CLI::write("Column fecha_fin_promo added.", "green");
            } else {
                CLI::write("Column fecha_fin_promo already exists.", "blue");
            }
        } catch (\Exception $e) {
            CLI::error("Failed: " . $e->getMessage());
        }
    }
}

