<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePreciosBase extends Migration
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
            'problema_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // NULL = aplica a todos los modelos de ese problema
            'modelo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'precio_mano_obra' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'precio_repuesto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
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
        $this->forge->addForeignKey('problema_id', 'problemas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('modelo_id', 'modelos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('precios_base');
    }

    public function down()
    {
        $this->forge->dropTable('precios_base', true);
    }
}
