<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearMarcas extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('marcas');

        // Índice para búsquedas rápidas
        $this->db->query('CREATE INDEX idx_tipo_dispositivo ON marcas(tipo_dispositivo_id)');
    }

    public function down()
    {
        $this->forge->dropTable('marcas');
    }
}
