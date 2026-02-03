<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaClientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cedula' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'unique' => true,
                'null' => false,
            ],
            'nombres' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'apellidos' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'telefono_secundario' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '150',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
