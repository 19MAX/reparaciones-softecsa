<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModificarChecklistItems extends Migration
{
    public function up()
    {
        // Agregar columna tipo_dispositivo_id
        $this->forge->addColumn('checklist_items', [
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true, // Si es null, aplica a TODOS
                'after' => 'id'
            ]
        ]);

        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('checklist_items', 'checklist_items_tipo_dispositivo_id_foreign');
        $this->forge->dropColumn('checklist_items', 'tipo_dispositivo_id');
    }
}
