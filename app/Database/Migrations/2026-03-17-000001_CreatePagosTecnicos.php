<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosTecnicos extends Migration
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
            // Relación 1:1 con la reparación finalizada
            'dispositivo_orden_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tecnico_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Monto de la comisión calculada
            'monto_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            // Snapshot del tipo de comisión al momento de finalizar
            'tipo_comision' => [
                'type'       => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default'    => 'porcentaje',
                'null'       => false,
            ],
            // Solo aplica cuando tipo_comision = 'porcentaje'
            'porcentaje_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            // Suma de mano_obra de los problemas resueltos (base del cálculo)
            'mano_obra_base' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            // Estado del pago al técnico
            'estado_pago' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'validado', 'pagado'],
                'default'    => 'pendiente',
                'null'       => false,
            ],
            // Fecha en que el dispositivo quedó en estado 'listo'
            'fecha_reparacion' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            // Fecha en que el admin validó el pago
            'fecha_validacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // Fecha en que se confirmó el pago efectivo
            'fecha_pago' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // Admin que realizó la última acción (validar o pagar)
            'validado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'observacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
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
        $this->forge->addUniqueKey('dispositivo_orden_id');
        $this->forge->addKey('tecnico_id');
        $this->forge->addKey('estado_pago');
        $this->forge->addForeignKey('dispositivo_orden_id', 'dispositivos_orden', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('validado_por', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pagos_tecnicos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos_tecnicos', true);
    }
}
