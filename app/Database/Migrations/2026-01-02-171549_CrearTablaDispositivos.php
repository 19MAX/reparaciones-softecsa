<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaDispositivos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'orden_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'tecnico_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            // Campos de marca/modelo
            'marca_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'modelo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'serie_imei' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'tipo_pass' => [
                'type' => 'ENUM',
                'constraint' => ['ninguno', 'pin', 'patron', 'contraseña', 'huella', 'facial'],
                'default' => 'ninguno',
                'comment' => 'Tipo de bloqueo del dispositivo',
            ],
            'pass_code' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Contraseña, PIN o descripción del patrón',
            ],

            // Campos de diagnóstico
            'estado_diagnostico' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'en_revision', 'diagnosticado', 'sin_diagnostico'],
                'default' => 'pendiente',
            ],
            'tiempo_diagnostico_minutos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'diagnostico_encontrado' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'diagnostico_detalle' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'diagnostico_cliente' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Sección para dar una versión simplificada para cliente',
            ],
            'costo_diagnostico_estimado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],

            // Decisión del cliente
            'cliente_autoriza_reparacion' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'fecha_respuesta_cliente' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'razon_rechazo' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Cobro de revisión
            'cobra_valor_revision' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'valor_revision_cobrado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'razon_cobro_revision' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha_estimada_entrega' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_entrega_real' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'requiere_cotizacion' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'prioridad_dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Anula prioridad de orden',
            ],

            // Campos de garantía (cache)
            'tiene_garantia_activa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'garantia_vence_en' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'veces_reclamada_garantia' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],


            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // LLAVE FORÁNEA
        $this->forge->addForeignKey('orden_id', 'ordenes_trabajo', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        // AGREGAR FOREIGN KEYS - prueba
        $this->forge->addForeignKey('marca_id', 'marcas', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('modelo_id', 'modelos', 'id', 'SET NULL', 'SET NULL');

        // FOREIGN KEY para prioridad -prueba
        $this->forge->addForeignKey('prioridad_dispositivo_id', 'urgencias', 'id', 'SET NULL', 'SET NULL');


        $this->forge->createTable('dispositivos');


        // AGREGAR ÍNDICES para optimizar búsquedas . PRUEBA
        $this->db->query('CREATE INDEX idx_garantia_activa ON dispositivos(tiene_garantia_activa, garantia_vence_en)');
        $this->db->query('CREATE INDEX idx_estado_diagnostico ON dispositivos(estado_diagnostico)');
        $this->db->query('CREATE INDEX idx_fecha_estimada ON dispositivos(fecha_estimada_entrega)');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivos');
    }
}
