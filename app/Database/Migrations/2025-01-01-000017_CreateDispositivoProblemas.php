<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * dispositivo_problemas
 * ---------------------
 * Tabla pivote que permite registrar MÚLTIPLES problemas por dispositivo.
 *
 * Lógica de campos desnormalizados en dispositivos_orden:
 *   - tiempo_total_horas = SUM(dp.tiempo_reparacion_horas)  → SUMA (conservador)
 *   - precio_total       = SUM(dp.precio_mano_obra + dp.precio_repuesto) + costo_prioridad
 *
 * Estos campos se recalculan desde el Model cada vez que se
 * inserta, actualiza o elimina un registro en esta tabla.
 *
 * Estadísticas de problemas frecuentes:
 *   SELECT p.nombre, COUNT(*) AS total
 *   FROM dispositivo_problemas dp
 *   JOIN problemas p ON p.id = dp.problema_id
 *   GROUP BY dp.problema_id
 *   ORDER BY total DESC;
 */
class CreateDispositivoProblemas extends Migration
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
            'dispositivo_orden_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'problema_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Copia del tiempo al momento del ingreso.
            // Se guarda aquí para que cambios futuros en el catálogo
            // no afecten órdenes ya registradas.
            'tiempo_reparacion_horas' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
            ],
            // Precio de mano de obra para ESTE problema en ESTE dispositivo.
            // Se precarga desde precios_base al seleccionar el problema,
            // pero el técnico puede ajustarlo.
            'precio_mano_obra' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            // Precio del repuesto para ESTE problema en ESTE dispositivo.
            // Se precarga desde precios_base, ajustable por el técnico.
            'precio_repuesto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            // Notas específicas del técnico sobre este problema puntual
            'observacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // // Estado individual del problema dentro del dispositivo
            // 'estado' => [
            //     'type'       => 'ENUM',
            //     'constraint' => ['pendiente', 'en_proceso', 'resuelto', 'no_reparable'],
            //     'default'    => 'pendiente',
            //     'null'       => false,
            // ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // Evita registrar el mismo problema dos veces en el mismo dispositivo
        $this->forge->addUniqueKey(['dispositivo_orden_id', 'problema_id']);

        // $this->forge->addKey('estado');

        $this->forge->addForeignKey('dispositivo_orden_id', 'dispositivos_orden', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('problema_id', 'problemas', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('dispositivo_problemas');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_problemas', true);
    }
}