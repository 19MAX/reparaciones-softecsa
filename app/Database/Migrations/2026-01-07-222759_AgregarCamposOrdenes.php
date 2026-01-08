<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarCamposOrdenes extends Migration
{
     public function up()
    {
        $fields = [
             'valor_revision' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'valor_repuestos'
            ],
            'mano_obra_aproximado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'valor_revision'
            ],
            'repuestos_aproximado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'mano_obra_aproximado'
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'repuestos_aproximado'
            ],
        ];

        $this->forge->addColumn('ordenes_trabajo', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('ordenes_trabajo', ['valor_revision', 'mano_obra_aproximado', 'repuestos_aproximado','total']);
    }
}
