<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaOrdenesTrabajo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'codigo_orden' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true
            ],
            'cliente_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'comment' => 'Cliente al que pertenece la orden',
            ],
            'usuario_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'comment' => 'Usuario que crea la orden',
            ],
            'urgencia_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'comment' => 'Nivel de urgencia de la orden',
            ],
            // PRUEBA
            'fecha_estimada_entrega' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'estado_global' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'en_evaluacion', 'finalizada', 'entregada', 'cancelada'],
                'default' => 'pendiente',
            ],
            'observaciones_generales' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones generales sobre la orden',
            ],
            'es_reclamo_garantia' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'orden_original_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            // ---
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // LLAVES FORÁNEAS
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'RESTRICT');
        // $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('urgencia_id', 'urgencias', 'id', 'SET NULL', 'RESTRICT');

        // PRUEBA
        $this->forge->addForeignKey('orden_original_id', 'ordenes_trabajo', 'id', 'SET NULL', 'CASCADE', 'ordenes_trabajo');


        $this->forge->createTable('ordenes_trabajo');
    }

    public function down()
    {
        $this->forge->dropTable('ordenes_trabajo');
    }
}
