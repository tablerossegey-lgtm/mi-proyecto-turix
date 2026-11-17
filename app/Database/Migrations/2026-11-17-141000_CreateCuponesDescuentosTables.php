<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateCuponesDescuentosTables extends Migration
{
    public function up()
    {
        // 1. Tabla principal de cupones (t_cupones)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'concepto' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'tipo_descuento' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'fijo',
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'compra_minima' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
            ],
            'descuento_maximo' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
            ],
            'fecha_emision' => [
                'type' => 'DATE',
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
            ],
            'fecha_vencimiento' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'limite_usos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'usos_realizados' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'motivo_origen' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'default'    => null,
            ],
            'observaciones' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'disponible',
            ],
            'cancelado_en' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'cancelado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'motivo_cancelacion' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
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
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->addKey('id_cliente');
        $this->forge->addKey('estado');
        $this->forge->createTable('t_cupones', true);

        // 2. Tabla de usos de cupones (t_cupones_usos)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_cupon' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'folio_compra' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'id_compra' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'fecha_uso' => [
                'type' => 'DATETIME',
            ],
            'subtotal_original' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'descuento_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'total_neto' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'aplicado',
            ],
            'revertido_en' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'revertido_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
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
                'constraint' => '100',
                'null'       => true,
                'default'    => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_cupon');
        $this->forge->addKey('id_cliente');
        $this->forge->addKey('folio_compra');
        $this->forge->createTable('t_cupones_usos', true);

        // 3. Bitácora de auditoría (t_cupones_bitacora)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_cupon' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'accion' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
            ],
            'folio_compra' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => null,
            ],
            'id_compra' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'monto_descuento' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
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
                'constraint' => '100',
                'null'       => true,
                'default'    => null,
            ],
            'creado_en' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_cupon');
        $this->forge->addKey('id_cliente');
        $this->forge->addKey('accion');
        $this->forge->createTable('t_cupones_bitacora', true);

        // 4. Columnas adicionales en t_cuenta_cliente (compatibles con registros históricos)
        $fieldsToAdd = [];
        $existingColumns = $this->db->getFieldNames('t_cuenta_cliente');

        if (!in_array('folio_compra', $existingColumns, true)) {
            $fieldsToAdd['folio_compra'] = [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('importe_original', $existingColumns, true)) {
            $fieldsToAdd['importe_original'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('descuento_aplicado', $existingColumns, true)) {
            $fieldsToAdd['descuento_aplicado'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ];
        }
        if (!in_array('id_cupon', $existingColumns, true)) {
            $fieldsToAdd['id_cupon'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('codigo_cupon', $existingColumns, true)) {
            $fieldsToAdd['codigo_cupon'] = [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('tipo_descuento_aplicado', $existingColumns, true)) {
            $fieldsToAdd['tipo_descuento_aplicado'] = [
                'type'       => 'VARCHAR',
                'constraint' => '25',
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('motivo_descuento', $existingColumns, true)) {
            $fieldsToAdd['motivo_descuento'] = [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
            ];
        }
        if (!in_array('gastos_envio', $existingColumns, true)) {
            $fieldsToAdd['gastos_envio'] = [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('t_cuenta_cliente', $fieldsToAdd);
        }
    }

    public function down()
    {
        $existingColumns = $this->db->getFieldNames('t_cuenta_cliente');
        $cols = [
            'folio_compra',
            'importe_original',
            'descuento_aplicado',
            'id_cupon',
            'codigo_cupon',
            'tipo_descuento_aplicado',
            'motivo_descuento',
            'gastos_envio',
        ];
        foreach ($cols as $col) {
            if (in_array($col, $existingColumns, true)) {
                $this->forge->dropColumn('t_cuenta_cliente', $col);
            }
        }

        $this->forge->dropTable('t_cupones_bitacora', true);
        $this->forge->dropTable('t_cupones_usos', true);
        $this->forge->dropTable('t_cupones', true);
    }
}

