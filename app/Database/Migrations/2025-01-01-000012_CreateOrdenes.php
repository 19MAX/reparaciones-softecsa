<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdenes extends Migration
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
            'numero_orden' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'cliente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'usuario_recepcion_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'listo', 'entregado', 'cancelado'],
                'default'    => 'pendiente',
                'null'       => false,
            ],
            'observaciones_generales' => [
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
        $this->forge->addUniqueKey('numero_orden');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('usuario_recepcion_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('ordenes');
    }

    public function down()
    {
        $this->forge->dropTable('ordenes', true);
    }
}
