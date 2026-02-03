<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearHistorialGarantias extends Migration
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
            'garantia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'reclamo_garantia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'accion' => [
                'type' => 'ENUM',
                'constraint' => ['creada', 'utilizada', 'rechazada', 'extendida', 'anulada'],
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
            'motivo' => [
                'type' => 'TEXT',
            ],
            'datos_adicionales' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('garantia_id', 'garantias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reclamo_garantia_id', 'reclamos_garantia', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('historial_garantias');

        $this->db->query('CREATE INDEX idx_garantia ON historial_garantias(garantia_id)');
        $this->db->query('CREATE INDEX idx_fecha ON historial_garantias(created_at)');
    }

    public function down()
    {
        $this->forge->dropTable('historial_garantias');
    }
}
