<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearDispositivoProblemas extends Migration
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
            'problema_comun_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            // 'problema_custom' => [
            //     'type' => 'TEXT',
            //     'null' => true,
            //     'comment' => 'Si no está en catálogo',
            // ],
            'prioridad' => [
                'type' => 'ENUM',
                'constraint' => ['alta', 'media', 'baja'],
                'default' => 'media',
            ],
            'diagnostico_inicial' => [
                'type' => 'TEXT',
                'comment' => 'Lo que dice el cliente del dispositivo',
            ],
            'diagnostico_tecnico' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Lo que encuentra el técnico',
            ],
            'fue_reparado' => [
                'type' => 'BOOLEAN',
                'null' => true,
                'comment' => 'null = en proceso',
            ],
            'razon_no_reparado' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'costo_reparacion' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'tiempo_invertido' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Minutos',
            ],
            'es_reparacion_garantia' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'reclamo_garantia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addForeignKey('problema_comun_id', 'problemas_comunes', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('dispositivo_problemas');

        $this->db->query('CREATE INDEX idx_dispositivo ON dispositivo_problemas(dispositivo_id)');
        $this->db->query('CREATE INDEX idx_problema_comun ON dispositivo_problemas(problema_comun_id)');
        $this->db->query('CREATE INDEX idx_reparado ON dispositivo_problemas(fue_reparado)');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_problemas');
    }

}
