<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaConfiguracionEmpresa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre_empresa' => [
                'type' => 'VARCHAR',
                'constraint' => 150
            ],
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true
            ],
            'direccion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('configuracion_empresa');
    }

    public function down()
    {
        $this->forge->dropTable('configuracion_empresa');
    }
}
