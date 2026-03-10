<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHorariosAtencion extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'dia_semana' => ['type' => 'INT', 'comment' => '0=Dom, 1=Lun, ..., 6=Sab'],
            'hora_apertura' => ['type' => 'TIME'],
            'hora_cierre' => ['type' => 'TIME'],
            'abierto' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('horarios_atencion');
    }

    public function down()
    {
        $this->forge->dropTable('horarios_atencion');

    }
}
