<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistorialEstados extends Migration
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
            'estado_anterior' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'estado_nuevo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'observacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'observacion_cliente' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('historial_estados');
    }

    public function down()
    {
        $this->forge->dropTable('historial_estados', true);
    }
}
