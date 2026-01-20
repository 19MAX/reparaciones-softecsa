<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarComisionesUsuarios extends Migration
{
    public function up()
    {
        $fields = [
            'tipo_comision' => [
                'type' => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default' => 'porcentaje',
                'null' => true,
                'after' => 'role',
                'comment' => 'Define si el valor es un % del total o un monto fijo por reparación'
            ],
            'valor_comision' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => true,
                'after' => 'tipo_comision',
                'comment' => 'Ej: 15.00 puede ser 15% o $15 según tipo_comision'
            ],
        ];

        $this->forge->addColumn('usuarios', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', ['tipo_comision', 'valor_comision']);
    }
}
