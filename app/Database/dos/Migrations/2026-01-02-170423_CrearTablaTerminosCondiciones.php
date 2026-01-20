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
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true, // Null = Termino General (aplica a todo)
                'after' => 'id'
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
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('terminos_condiciones');
    }
}
