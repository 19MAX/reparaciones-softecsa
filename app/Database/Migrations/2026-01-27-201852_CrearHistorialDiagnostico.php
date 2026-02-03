<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearHistorialDiagnostico extends Migration
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
            'fecha_inicio_diagnostico' => [
                'type' => 'DATETIME',
            ],
            'fecha_fin_diagnostico' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tiempo_invertido_minutos' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'diagnostico_encontrado' => [
                'type' => 'BOOLEAN',
            ],
            'detalle_tecnico' => [
                'type' => 'TEXT',
            ],
            'detalle_cliente' => [
                'type' => 'TEXT',
                'comment' => 'Versión simplificada',
            ],
            'fotos_diagnostico' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'costo_estimado_reparacion' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'costo_estimado_repuestos' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'observaciones_internas' => [
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
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('historial_diagnostico');

        $this->db->query('CREATE INDEX idx_dispositivo_tecnico ON historial_diagnostico(dispositivo_id, tecnico_id)');
        $this->db->query('CREATE INDEX idx_fecha ON historial_diagnostico(fecha_inicio_diagnostico)');
    }

    public function down()
    {
        $this->forge->dropTable('historial_diagnostico');
    }
}
