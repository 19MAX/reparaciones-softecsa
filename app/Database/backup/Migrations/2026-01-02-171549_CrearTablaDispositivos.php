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
                'after' => 'orden_id'
            ],
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'marca' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 100
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
            'problema_reportado' => [
                'type' => 'TEXT'
            ],
            'estado_reparacion' => [
                'type' => 'TINYINT',
                'unsigned' => true,
                'default' => 1
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'mano_obra' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => false,
                'comment' => 'Costo de mano de obra para reparar este dispositivo'
            ],
            'valor_repuestos' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => false,
                'comment' => 'Costo de repuestos utilizados en este dispositivo'
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        // LLAVE FORÁNEA
        $this->forge->addForeignKey('orden_id', 'ordenes_trabajo', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('dispositivos');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivos');
    }
}
