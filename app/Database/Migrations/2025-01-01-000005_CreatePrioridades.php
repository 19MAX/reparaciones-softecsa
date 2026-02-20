<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrioridades extends Migration
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
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'costo_adicional' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'tiempo_maximo_horas' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => '24.00',
                'null'       => false,
            ],
            'color_badge' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'secondary',
                'null'       => true,
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
        $this->forge->createTable('prioridades');
    }

    public function down()
    {
        $this->forge->dropTable('prioridades', true);
    }
}
