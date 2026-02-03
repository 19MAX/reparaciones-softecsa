<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearGarantias extends Migration
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
            'orden_original_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tecnico_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_garantia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'problema_reparado_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a dispositivo_problemas',
            ],

            // FECHAS
            'fecha_inicio' => [
                'type' => 'DATE',
                'comment' => 'Fecha de entrega al cliente',
            ],
            'fecha_vencimiento' => [
                'type' => 'DATE',
            ],
            'dias_garantia' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Snapshot del valor',
            ],

            // ESTADO
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['activa', 'vencida', 'utilizada', 'anulada'],
                'default' => 'activa',
            ],
            'fue_utilizada' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],

            // CONDICIONES
            'condiciones_aceptadas_cliente' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'texto_condiciones' => [
                'type' => 'TEXT',
                'comment' => 'Snapshot',
            ],
            'exclusiones' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // REEMPLAZO DE GARANTÍA
            'es_reemplazo_garantia' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'garantia_original_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],

            // TRACKING
            'notificacion_proximo_vencimiento' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addForeignKey('orden_original_id', 'ordenes_trabajo', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tipo_garantia_id', 'tipos_garantia', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('problema_reparado_id', 'dispositivo_problemas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('garantia_original_id', 'garantias', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('garantias');

        $this->db->query('CREATE INDEX idx_estado_vencimiento ON garantias(estado, fecha_vencimiento)');
        $this->db->query('CREATE INDEX idx_dispositivo ON garantias(dispositivo_id)');
    }

    public function down()
    {
        $this->forge->dropTable('garantias');
    }
}
