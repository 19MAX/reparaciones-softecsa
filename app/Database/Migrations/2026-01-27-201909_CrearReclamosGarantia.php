<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearReclamosGarantia extends Migration
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
            'dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'orden_reclamo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'fecha_reclamo' => [
                'type' => 'DATETIME',
            ],
            'usuario_recepcion_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],

            // DESCRIPCIÓN
            'problema_reportado_cliente' => [
                'type' => 'TEXT',
            ],
            'problema_es_mismo' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],

            // EVALUACIÓN
            'tecnico_evaluador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'fecha_evaluacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'evaluacion_tecnica' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'problema_cubierto_garantia' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'razon_rechazo_garantia' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // EVIDENCIA
            'fotos_evaluacion' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'evidencia_mal_uso' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'descripcion_evidencia' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // DECISIÓN
            'estado_reclamo' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente_evaluacion', 'aprobado', 'rechazado', 'reparado', 'entregado'],
                'default' => 'pendiente_evaluacion',
            ],
            'aprobado_por_admin' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'admin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'comentario_decision' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // COSTOS
            'costo_adicional_cliente' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'razon_costo_adicional' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // SATISFACCIÓN
            'cliente_satisfecho' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'comentario_cliente' => [
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
        $this->forge->addForeignKey('garantia_id', 'garantias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('orden_reclamo_id', 'ordenes_trabajo', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('usuario_recepcion_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tecnico_evaluador_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('admin_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('reclamos_garantia');

        // Agregar FK faltante a dispositivo_problemas
        $this->db->query('
            ALTER TABLE dispositivo_problemas 
            ADD CONSTRAINT fk_reclamo_garantia 
            FOREIGN KEY (reclamo_garantia_id) 
            REFERENCES reclamos_garantia(id) 
            ON DELETE SET NULL 
            ON UPDATE SET NULL
        ');

        $this->db->query('CREATE INDEX idx_garantia ON reclamos_garantia(garantia_id)');
        $this->db->query('CREATE INDEX idx_estado ON reclamos_garantia(estado_reclamo)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE dispositivo_problemas DROP FOREIGN KEY fk_reclamo_garantia');
        $this->forge->dropTable('reclamos_garantia');
    }
}
