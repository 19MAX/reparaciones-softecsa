<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaUrgencias extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'recargo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'tiempo_espera' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('urgencias');
    }

    public function down()
    {
        $this->forge->dropTable('urgencias');
    }
}
