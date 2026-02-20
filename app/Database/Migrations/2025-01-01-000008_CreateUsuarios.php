<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarios extends Migration
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
            'cedula' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
                'null'       => false,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'apellido' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'rol' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'tecnico', 'recepcion'],
                'default'    => 'recepcion',
                'null'       => false,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
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
        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios', true);
    }
}
