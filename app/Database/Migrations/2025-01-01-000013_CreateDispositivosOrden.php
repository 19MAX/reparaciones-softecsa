<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDispositivosOrden extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'orden_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'tipo_dispositivo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'marca_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // NULL si el modelo no está en catálogo (usar modelo_texto)
            'modelo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'modelo_texto' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'serie_imei' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'problema_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // NULL si no se eligió prioridad
            'prioridad_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            // NULL si aún no está asignado
            'tecnico_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'relato_cliente' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tipo_seguridad' => [
                'type'       => 'ENUM',
                'constraint' => ['sin_clave', 'pin', 'patron', 'contrasena', 'huella'],
                'default'    => 'sin_clave',
                'null'       => false,
            ],
            // Almacenar cifrado si se proporciona
            'clave_acceso' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'precio_mano_obra' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'precio_repuesto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'costo_prioridad' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'precio_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            // Se calcula al cerrar la reparación
            'comision_tecnico' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'fecha_estimada_entrega' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_real_entrega' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'listo', 'entregado', 'cancelado'],
                'default'    => 'pendiente',
                'null'       => false,
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

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('estado');
        $this->forge->addKey('tecnico_id');
        $this->forge->addForeignKey('orden_id', 'ordenes', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('marca_id', 'marcas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('modelo_id', 'modelos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('problema_id', 'problemas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('prioridad_id', 'prioridades', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('tecnico_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('dispositivos_orden');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivos_orden', true);
    }
}
