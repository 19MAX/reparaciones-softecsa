<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaDispositivos extends Migration
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
                'unsigned' => true
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'marca' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'serie_imei' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'problema_reportado' => [
                'type' => 'TEXT'
            ],
            'estado_reparacion' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // LLAVE FORÁNEA
        $this->forge->addForeignKey('orden_id', 'ordenes_trabajo', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('dispositivos');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivos');
    }
}
