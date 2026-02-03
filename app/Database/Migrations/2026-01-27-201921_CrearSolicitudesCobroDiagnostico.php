<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearSolicitudesCobroDiagnostico extends Migration
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
            'tecnico_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tiempo_invertido_minutos' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'justificacion' => [
                'type' => 'TEXT',
            ],
            'valor_solicitado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'aprobada', 'rechazada'],
                'default' => 'pendiente',
            ],
            'admin_revisor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'comentario_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha_revision' => [
                'type' => 'DATETIME',
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
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admin_revisor_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('solicitudes_cobro_diagnostico');

        $this->db->query('CREATE INDEX idx_estado ON solicitudes_cobro_diagnostico(estado)');
        $this->db->query('CREATE INDEX idx_tecnico ON solicitudes_cobro_diagnostico(tecnico_id)');
    }

    public function down()
    {
        $this->forge->dropTable('solicitudes_cobro_diagnostico');
    }
}
