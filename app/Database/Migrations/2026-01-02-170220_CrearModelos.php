<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearModelos extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'marca_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('marca_id', 'marcas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('modelos');

        $this->db->query('CREATE INDEX idx_marca ON modelos(marca_id)');
    }

    public function down()
    {
        $this->forge->dropTable('modelos');
    }
}
