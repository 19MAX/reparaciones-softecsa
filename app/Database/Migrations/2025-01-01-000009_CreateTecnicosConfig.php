<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTecnicosConfig extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tipo_comision' => [
                'type'       => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default'    => 'porcentaje',
                'null'       => false,
            ],
            'valor_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'especialidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('usuario_id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tecnicos_config');
    }

    public function down()
    {
        $this->forge->dropTable('tecnicos_config', true);
    }
}
