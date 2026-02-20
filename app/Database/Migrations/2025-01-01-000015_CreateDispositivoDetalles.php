<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDispositivoDetalles extends Migration
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
            'dispositivo_orden_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // NULL si se ingresa texto libre
            'detalle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'detalle_texto' => [
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
        $this->forge->addForeignKey('dispositivo_orden_id', 'dispositivos_orden', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('detalle_id', 'detalles_catalogo', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('dispositivo_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_detalles', true);
    }
}
