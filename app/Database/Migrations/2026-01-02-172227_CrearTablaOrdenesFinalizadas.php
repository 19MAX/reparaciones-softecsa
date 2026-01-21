<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaOrdenesFinalizadas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'orden_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
                'comment' => 'ID de la orden en ordenes_trabajo'
            ],
            'fecha_finalizacion' => [
                'type' => 'DATETIME',
                'null' => false
            ],
            'mano_obra_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Suma de mano_obra de todos los dispositivos'
            ],
            'repuestos_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Suma de valor_repuestos de todos los dispositivos'
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Total cobrado al cliente'
            ],
            'ganancia_total_tecnicos' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Suma de ganancias de todos los técnicos'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('orden_id', 'ordenes_trabajo', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('ordenes_finalizadas');
    }

    public function down()
    {
        $this->forge->dropTable('ordenes_finalizadas');
    }
}
