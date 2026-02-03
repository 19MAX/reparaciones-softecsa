<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearDispositivoAccesorios extends Migration
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
            'dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'accesorio_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['bueno', 'regular', 'malo'],
                'default' => 'bueno',
            ],
            'observacion' => [
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('accesorio_id', 'accesorios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dispositivo_accesorios');

        $this->db->query('CREATE INDEX idx_dispositivo ON dispositivo_accesorios(dispositivo_id)');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_accesorios');
    }
}
