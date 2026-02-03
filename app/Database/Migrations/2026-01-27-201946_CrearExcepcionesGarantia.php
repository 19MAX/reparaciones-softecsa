<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearExcepcionesGarantia extends Migration
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
            'garantia_vencida_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'dias_vencida' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'justificacion_empleado' => [
                'type' => 'TEXT',
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'aprobada', 'rechazada'],
                'default' => 'pendiente',
            ],
            'solicitante_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'admin_revisor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'decision_admin' => [
                'type' => 'ENUM',
                'constraint' => ['aprobar_gratis', 'aprobar_descuento_50', 'rechazar'],
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
        $this->forge->addForeignKey('garantia_vencida_id', 'garantias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('solicitante_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admin_revisor_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('excepciones_garantia');

        $this->db->query('CREATE INDEX idx_estado ON excepciones_garantia(estado)');
    }

    public function down()
    {
        $this->forge->dropTable('excepciones_garantia');
    }
}
