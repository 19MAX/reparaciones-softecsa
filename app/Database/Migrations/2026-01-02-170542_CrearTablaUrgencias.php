<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaUrgencias extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'recargo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'tiempo_espera' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],
            //prueba
            'color_hex' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#6c757d',
                'after' => 'nombre',
                'comment' => 'Color para UI (ej: #ff0000)',
            ],
            'orden_prioridad' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'color_hex',
                'comment' => 'Para ordenar (menor = más urgente)',
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('urgencias');
        $this->db->query('CREATE INDEX idx_orden_prioridad ON urgencias(orden_prioridad)');

    }

    public function down()
    {
        $this->forge->dropTable('urgencias');
    }
}
