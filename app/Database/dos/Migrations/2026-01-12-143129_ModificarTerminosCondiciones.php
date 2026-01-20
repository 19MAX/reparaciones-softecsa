<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModificarTerminosCondiciones extends Migration
{
    public function up()
    {
        $this->forge->addColumn('terminos_condiciones', [
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true, // Null = Termino General (aplica a todo)
                'after' => 'id'
            ]
        ]);

        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('terminos_condiciones', 'terminos_condiciones_tipo_dispositivo_id_foreign');
        $this->forge->dropColumn('terminos_condiciones', 'tipo_dispositivo_id');
    }
}
