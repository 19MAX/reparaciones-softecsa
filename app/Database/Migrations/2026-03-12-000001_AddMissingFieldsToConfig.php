<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingFieldsToConfig extends Migration
{
    public function up()
    {
        $fields = [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'nombre_empresa'
            ],
            'valor_revision' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'direccion'
            ],
        ];
        $this->forge->addColumn('configuracion_empresa', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('configuracion_empresa', ['email', 'valor_revision']);
    }
}
