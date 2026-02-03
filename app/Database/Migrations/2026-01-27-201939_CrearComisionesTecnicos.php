<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearComisionesTecnicos extends Migration
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
            'orden_finalizada_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL si es diagnóstico sin reparación',
            ],
            'tecnico_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_comision' => [
                'type' => 'ENUM',
                'constraint' => ['reparacion', 'diagnostico', 'garantia_sin_pago', 'garantia_cubierta_negocio'],
                'default' => 'reparacion',
            ],
            'mano_obra_dispositivo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Base para calcular comisión',
            ],
            'tipo_calculo' => [
                'type' => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'comment' => 'Snapshot del tipo al momento',
            ],
            'valor_comision_config' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Snapshot del valor configurado (ej: 50 para 50%)',
            ],
            'comision_calculada' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Resultado final',
            ],
            'tiempo_invertido' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Minutos dedicados',
            ],
            'observacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pagado' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'fecha_pago' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'metodo_pago' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
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
        $this->forge->addForeignKey('orden_finalizada_id', 'ordenes_finalizadas', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dispositivo_id', 'dispositivos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('comisiones_tecnicos');

        $this->db->query('CREATE INDEX idx_tecnico_pagado ON comisiones_tecnicos(tecnico_id, pagado)');
        $this->db->query('CREATE INDEX idx_orden ON comisiones_tecnicos(orden_finalizada_id)');
        $this->db->query('CREATE INDEX idx_fecha_pago ON comisiones_tecnicos(fecha_pago)');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones_tecnicos');
    }
}
