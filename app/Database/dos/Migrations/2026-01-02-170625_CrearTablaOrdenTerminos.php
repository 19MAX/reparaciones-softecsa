<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaOrdenTerminos extends Migration
{
 public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'orden_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'termino_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'orden' => [
                'type' => 'INT',
                'default' => 1
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey(
            'orden_id',
            'ordenes_trabajo',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->forge->addForeignKey(
            'termino_id',
            'terminos_condiciones',
            'id',
            'RESTRICT',
            'RESTRICT'
        );

        $this->forge->createTable('orden_terminos');
    }

    public function down()
    {
        $this->forge->dropTable('orden_terminos');
    }
}
