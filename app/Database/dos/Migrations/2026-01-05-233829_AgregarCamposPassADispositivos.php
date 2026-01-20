<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarCamposPassADispositivos extends Migration
{
    public function up()
    {
        $fields = [
            'tipo_pass' => [
                'type' => 'ENUM',
                'constraint' => ['ninguno', 'pin', 'patron', 'contraseña', 'huella', 'facial'],
                'default' => 'ninguno',
                'comment' => 'Tipo de bloqueo del dispositivo',
                'after' => 'serie_imei'
            ],
            'pass_code' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Contraseña, PIN o descripción del patrón',
                'after' => 'tipo_pass'
            ]
        ];

        $this->forge->addColumn('dispositivos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('dispositivos', ['tipo_pass', 'pass_code']);
    }
}
