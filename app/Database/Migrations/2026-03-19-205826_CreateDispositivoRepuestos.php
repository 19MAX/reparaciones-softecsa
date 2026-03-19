<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDispositivoRepuestos extends Migration
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
            'dispositivo_problema_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'repuesto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'cantidad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
            ],
            'valor_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'valor_total' => [
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
        $this->forge->addForeignKey('dispositivo_problema_id', 'dispositivo_problemas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('repuesto_id', 'repuestos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('dispositivo_repuestos');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_repuestos');
    }
}
