<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModificarDispositivos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dispositivos', [
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'orden_id'
            ]
        ]);
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'RESTRICT', 'RESTRICT');

    }

    public function down()
    {
        // Primero eliminar la foreign key
        $this->forge->dropForeignKey('dispositivos', 'dispositivos_tipo_dispositivo_id_foreign');

        // Luego eliminar la columna
        $this->forge->dropColumn('dispositivos', 'tipo_dispositivo_id');
    }

}
