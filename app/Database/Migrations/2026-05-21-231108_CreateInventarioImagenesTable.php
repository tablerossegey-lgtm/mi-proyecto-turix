<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarioImagenesTable extends Migration
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
            ],
            'ruta_foto' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'orden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_producto'); // Index for fast search
        $this->forge->addForeignKey('id_producto', 't_inventario', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('t_inventario_imagenes');
    }

    public function down()
    {
        $this->forge->dropTable('t_inventario_imagenes');
    }
}
