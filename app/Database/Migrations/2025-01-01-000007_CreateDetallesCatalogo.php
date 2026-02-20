<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDetallesCatalogo extends Migration
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
            'tipo_dispositivo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
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
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('detalles_catalogo');
    }

    public function down()
    {
        $this->forge->dropTable('detalles_catalogo', true);
    }
}
