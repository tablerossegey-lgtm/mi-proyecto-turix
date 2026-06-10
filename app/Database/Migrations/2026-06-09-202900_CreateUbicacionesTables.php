<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUbicacionesTables extends Migration
{
    public function up()
    {
        // 1. Crear tabla t_ubicaciones
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('t_ubicaciones');

        // Insertar ubicaciones iniciales por defecto (Casa y Oficina)
        $this->db->table('t_ubicaciones')->insertBatch([
            ['id' => 1, 'nombre' => 'Casa'],
            ['id' => 2, 'nombre' => 'Oficina'],
        ]);

        // 2. Crear tabla t_inventario_ubicaciones
        $this->forge->addField([
            'id_producto' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_ubicacion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'cantidad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey(['id_producto', 'id_ubicacion'], true);
        $this->forge->addForeignKey('id_producto', 't_inventario', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_ubicacion', 't_ubicaciones', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('t_inventario_ubicaciones');
    }

    public function down()
    {
        $this->forge->dropTable('t_inventario_ubicaciones');
        $this->forge->dropTable('t_ubicaciones');
    }
}
