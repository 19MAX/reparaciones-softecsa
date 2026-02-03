<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaDispositivoImagenes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'dispositivo_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['ingreso', 'salida', 'diagnostico', 'evidencia_garantia', 'reparacion'],
                'default' => 'ingreso',
            ],
            'ruta_imagen' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],

            //prueba
            'dispositivo_problema_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Si es foto de un problema específico',
            ],
            'reclamo_garantia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Si es evidencia de reclamo',
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tomada_por_usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey(
            'dispositivo_id',
            'dispositivos',
            'id',
            'CASCADE',
            'CASCADE'
        );


        // Agregar foreign keys - prueba
        $this->forge->addForeignKey('dispositivo_problema_id', 'dispositivo_problemas', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('reclamo_garantia_id', 'reclamos_garantia', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('tomada_por_usuario_id', 'usuarios', 'id', 'SET NULL', 'SET NULL');


        $this->forge->createTable('dispositivo_imagenes');
        // --PRUEBA
        $this->db->query('CREATE INDEX idx_tipo ON dispositivo_imagenes(tipo)');
        $this->db->query('CREATE INDEX idx_problema ON dispositivo_imagenes(dispositivo_problema_id)');

    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_imagenes');
    }
}
