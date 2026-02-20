<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDispositivoAccesorios extends Migration
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
            'accesorio_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'accesorio_texto' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'cantidad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
            ],
            'observacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addForeignKey('accesorio_id', 'accesorios_catalogo', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('dispositivo_accesorios');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_accesorios', true);
    }
}
