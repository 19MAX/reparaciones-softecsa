<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaOrdenesTrabajo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'codigo_orden' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true
            ],
            'cliente_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'usuario_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'tecnico_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'urgencia_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'mano_obra' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'valor_repuestos' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'valor_revision' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'mano_obra_aproximado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'repuestos_aproximado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'abono' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'estado' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'default' => 1
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // LLAVES FORÁNEAS
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('urgencia_id', 'urgencias', 'id', 'SET NULL', 'RESTRICT');

        $this->forge->createTable('ordenes_trabajo');
    }

    public function down()
    {
        $this->forge->dropTable('ordenes_trabajo');
    }
}
