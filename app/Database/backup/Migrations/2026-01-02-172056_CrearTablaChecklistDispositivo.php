<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaChecklistDispositivo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'checklist_item_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'estado' => [
                'type' => 'BOOLEAN'
            ],
            'observacion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey(
            'dispositivo_id',
            'dispositivos',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->forge->addForeignKey(
            'checklist_item_id',
            'checklist_items',
            'id',
            'RESTRICT',
            'RESTRICT'
        );

        $this->forge->createTable('checklist_dispositivo');
    }

    public function down()
    {
        $this->forge->dropTable('checklist_dispositivo');
    }
}
