<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaTerminosCondiciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'titulo' => [
                'type' => 'VARCHAR',
                'constraint' => 150
            ],
            'contenido' => [
                'type' => 'TEXT'
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('terminos_condiciones');
    }

    public function down()
    {
        $this->forge->dropTable('terminos_condiciones');
    }
}
