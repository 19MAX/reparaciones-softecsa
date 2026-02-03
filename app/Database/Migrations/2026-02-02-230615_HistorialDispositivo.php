<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HistorialDispositivo extends Migration
{

    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'estado_anterior' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'estado_nuevo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'comentario' => [
                'type' => 'TEXT',
                'coment' => 'Comentario sobre el cambio de estado',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('historial_dispositivo');
    }

    public function down()
    {
        $this->forge->dropTable('historial_dispositivo');
    }
}
