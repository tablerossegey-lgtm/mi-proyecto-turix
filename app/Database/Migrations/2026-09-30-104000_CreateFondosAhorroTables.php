<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFondosAhorroTables extends Migration
{
    public function up()
    {
        // 1. Tabla de Fondos (t_fondos)
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
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'porcentaje_sugerido' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 10.00,
            ],
            'meta_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'actualizado_en' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('t_fondos', true);

        // 2. Tabla de Aportaciones (t_fondos_aportaciones)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_fondo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'anio' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'mes' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'balance_caja' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'porcentaje_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 10.00,
            ],
            'monto_sugerido' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'monto_aportado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'tipo_aportacion' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'Mensual',
            ],
            'fecha_registro' => [
                'type' => 'DATE',
            ],
            'notas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_fondo');
        $this->forge->addUniqueKey(['id_fondo', 'anio', 'mes', 'tipo_aportacion'], 'unique_fondo_mes_tipo');
        $this->forge->addForeignKey('id_fondo', 't_fondos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('t_fondos_aportaciones', true);

        // 3. Registrar el fondo inicial "Bodega Turixshop" si no existe
        $db = \Config\Database::connect();
        $builder = $db->table('t_fondos');
        $existe = $builder->where('nombre', 'Bodega Turixshop')->countAllResults();
        if ($existe == 0) {
            $builder->insert([
                'nombre'              => 'Bodega Turixshop',
                'descripcion'         => 'Fondo destinado a la construcción y acondicionamiento de la bodega propia.',
                'porcentaje_sugerido' => 10.00,
                'meta_monto'          => 30000.00,
                'activo'              => 1,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('t_fondos_aportaciones', true);
        $this->forge->dropTable('t_fondos', true);
    }
}
