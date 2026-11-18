<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateRifasDinamicasTables extends Migration
{
    public function up()
    {
        // 1. Tabla principal de dinámicas y rifas (t_dinamicas)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '180',
            ],
            'descripcion' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'imagen_promo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
            ],
            'fecha_sorteo' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
            ],
            'hora_sorteo' => [
                'type'    => 'TIME',
                'null'    => true,
                'default' => null,
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'modalidad_sorteo' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'por_definir',
            ],
            'plataforma_digital' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'default'    => null,
            ],
            'url_plataforma' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'tipo_requisito' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'default'    => 'acumulado_unico',
            ],
            'importe_minimo' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'max_participaciones_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => 1,
            ],
            'max_premios_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => 1,
            ],
            'permitir_clientes_ocasionales' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'permitir_registro_manual' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'requiere_compra_liquidada' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'plantilla_whatsapp' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '35',
                'default'    => 'borrador',
            ],
            'listado_cerrado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'cerrado_en' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'cerrado_por_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'cerrado_por_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '120',
                'null'       => true,
                'default'    => null,
            ],
            'resolucion_cierre' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'creado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'actualizado_en' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('estado', false, false, 'idx_dinamicas_estado');
        $this->forge->addKey(['fecha_inicio', 'fecha_fin'], false, false, 'idx_dinamicas_fechas');
        $this->forge->createTable('t_dinamicas', true);

        // 2. Tabla de participantes de dinámicas (t_dinamica_participantes)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_dinamica' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'es_ocasional' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'numero_control' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nombre_participante' => [
                'type'       => 'VARCHAR',
                'constraint' => '180',
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'null'       => true,
                'default'    => null,
            ],
            'importe_acumulado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'num_compras_validas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'num_participaciones' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'fecha_cumplimiento' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
            ],
            'origen' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'automatico',
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '25',
                'default'    => 'elegible',
            ],
            'motivo_estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'referencia_compra' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'default'    => null,
            ],
            'motivo_registro' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'notificado_whatsapp' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'fecha_notificacion' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'notificado_por_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '120',
                'null'       => true,
                'default'    => null,
            ],
            'en_listado_definitivo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'detalle_compras_json' => [
                'type'    => 'MEDIUMTEXT',
                'null'    => true,
                'default' => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'actualizado_en' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_dinamica', 'id_cliente', 'origen'], 'uq_dinamica_cliente_origen');
        $this->forge->addUniqueKey(['id_dinamica', 'numero_control'], 'uq_dinamica_numero_control');
        $this->forge->addKey(['id_dinamica', 'estado'], false, false, 'idx_part_dinamica_estado');
        $this->forge->addKey('id_cliente', false, false, 'idx_part_cliente');
        $this->forge->addForeignKey('id_dinamica', 't_dinamicas', 'id', 'CASCADE', 'CASCADE', 'fk_part_dinamica');
        $this->forge->createTable('t_dinamica_participantes', true);

        // 3. Tabla de premios de dinámicas (t_dinamica_premios)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_dinamica' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'orden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'nombre_publico' => [
                'type'       => 'VARCHAR',
                'constraint' => '180',
            ],
            'descripcion_admin' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'imagen' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'id_producto' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'valor_estimado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'estado_entrega' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'pendiente_asignar',
            ],
            'inventario_descontado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'actualizado_en' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_dinamica', 'orden'], false, false, 'idx_premio_dinamica_orden');
        $this->forge->addForeignKey('id_dinamica', 't_dinamicas', 'id', 'CASCADE', 'CASCADE', 'fk_premio_dinamica');
        $this->forge->createTable('t_dinamica_premios', true);

        // 4. Tabla de ganadores y entrega de premios (t_dinamica_ganadores)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_dinamica' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_premio' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_participante' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'nombre_ganador' => [
                'type'       => 'VARCHAR',
                'constraint' => '180',
            ],
            'numero_participacion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'fecha_hora_sorteo' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'modalidad_sorteo' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'por_definir',
            ],
            'enlace_resultado' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'evidencia_sorteo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'fecha_notificacion' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'fecha_entrega' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'usuario_responsable' => [
                'type'       => 'VARCHAR',
                'constraint' => '120',
                'null'       => true,
                'default'    => null,
            ],
            'evidencia_entrega' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'observaciones_entrega' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'actualizado_en' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('id_premio', 'uq_ganador_premio');
        $this->forge->addKey(['id_dinamica', 'id_participante'], false, false, 'idx_ganador_dinamica_part');
        $this->forge->addForeignKey('id_dinamica', 't_dinamicas', 'id', 'CASCADE', 'CASCADE', 'fk_ganador_dinamica');
        $this->forge->addForeignKey('id_premio', 't_dinamica_premios', 'id', 'CASCADE', 'CASCADE', 'fk_ganador_premio');
        $this->forge->addForeignKey('id_participante', 't_dinamica_participantes', 'id', 'CASCADE', 'CASCADE', 'fk_ganador_participante');
        $this->forge->createTable('t_dinamica_ganadores', true);

        // 5. Bitácora de auditoría de dinámicas (t_dinamicas_bitacora)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_dinamica' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_participante' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'id_premio' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'accion' => [
                'type'       => 'VARCHAR',
                'constraint' => '60',
            ],
            'motivo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ],
            'detalle' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'usuario_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '120',
                'null'       => true,
                'default'    => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_dinamica', 'creado_en'], false, false, 'idx_bitacora_dinamica_fecha');
        $this->forge->addForeignKey('id_dinamica', 't_dinamicas', 'id', 'CASCADE', 'CASCADE', 'fk_bitacora_dinamica');
        $this->forge->createTable('t_dinamicas_bitacora', true);
    }

    public function down()
    {
        $this->forge->dropTable('t_dinamicas_bitacora', true);
        $this->forge->dropTable('t_dinamica_ganadores', true);
        $this->forge->dropTable('t_dinamica_premios', true);
        $this->forge->dropTable('t_dinamica_participantes', true);
        $this->forge->dropTable('t_dinamicas', true);
    }
}
