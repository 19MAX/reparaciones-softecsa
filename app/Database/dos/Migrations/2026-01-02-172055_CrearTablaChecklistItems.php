<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaChecklistItems extends Migration
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
                'constraint' => 150
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('checklist_items');
    }

    public function down()
    {
        $this->forge->dropTable('checklist_items');
    }
}
