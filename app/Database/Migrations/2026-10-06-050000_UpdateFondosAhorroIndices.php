<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFondosAhorroIndices extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Verificar si existe el índice previo unique_fondo_mes_tipo y eliminarlo
        $indices = $db->query("SHOW INDEX FROM t_fondos_aportaciones WHERE Key_name = 'unique_fondo_mes_tipo'")->getResultArray();
        if (!empty($indices)) {
            $this->forge->dropKey('t_fondos_aportaciones', 'unique_fondo_mes_tipo', false);
        }

        // 2. Agregar columna virtual mes_mensual para restringir unicidad SOLO a las aportaciones mensuales
        $fields = $db->getFieldNames('t_fondos_aportaciones');
        if (!in_array('mes_mensual', $fields)) {
            $db->query("ALTER TABLE t_fondos_aportaciones ADD COLUMN mes_mensual INT GENERATED ALWAYS AS (IF(tipo_aportacion = 'Mensual', mes, NULL)) VIRTUAL AFTER mes");
        }

        // 3. Agregar índice único que solo aplica a mensuales (donde mes_mensual no es NULL)
        $indicesMensual = $db->query("SHOW INDEX FROM t_fondos_aportaciones WHERE Key_name = 'unique_fondo_mes_mensual'")->getResultArray();
        if (empty($indicesMensual)) {
            $db->query("ALTER TABLE t_fondos_aportaciones ADD UNIQUE KEY unique_fondo_mes_mensual (id_fondo, anio, mes_mensual)");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $indicesMensual = $db->query("SHOW INDEX FROM t_fondos_aportaciones WHERE Key_name = 'unique_fondo_mes_mensual'")->getResultArray();
        if (!empty($indicesMensual)) {
            $db->query("ALTER TABLE t_fondos_aportaciones DROP INDEX unique_fondo_mes_mensual");
        }

        $fields = $db->getFieldNames('t_fondos_aportaciones');
        if (in_array('mes_mensual', $fields)) {
            $db->query("ALTER TABLE t_fondos_aportaciones DROP COLUMN mes_mensual");
        }

        $this->forge->addUniqueKey(['id_fondo', 'anio', 'mes', 'tipo_aportacion'], 'unique_fondo_mes_tipo');
    }
}
