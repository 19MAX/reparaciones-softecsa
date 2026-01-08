<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarRevisionEmpresa extends Migration
{
    public function up()
    {
        $fields = [
            'valor_revision' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Costo base por revisión o diagnóstico',
                'after' => 'direccion' // Para ordenarlo visualmente
            ],
        ];

        $this->forge->addColumn('configuracion_empresa', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('configuracion_empresa', 'valor_revision');
    }
}
