<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidosEncargadosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_producto' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'nombre_cliente' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'contacto_cliente' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'cantidad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'anticipo' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'Pendiente',
            ],
            'notas' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_producto');
        $this->forge->addForeignKey('id_producto', 't_inventario', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('t_pedidos_encargados');
    }

    public function down()
    {
        $this->forge->dropTable('t_pedidos_encargados');
    }
}
