<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaHistorialDispositivos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'usuario_id' => [ // El técnico o recepcionista que hizo la acción
                'type' => 'INT',
                'unsigned' => true
            ],
            'estado_anterior' => [ // Para saber de qué estado venía
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'estado_nuevo' => [ // El estado al que cambió (ej: en_reparacion, entregado)
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'comentario' => [ // Lo que el técnico escribe sobre el cambio
                'type' => 'TEXT',
                'null' => true
            ],
            'es_visible_cliente' => [ // Opcional: Si el cliente puede ver este comentario
                'type' => 'BOOLEAN',
                'default' => false
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('historial_dispositivos');
    }

    public function down()
    {
        $this->forge->dropTable('historial_dispositivos');
    }
}
