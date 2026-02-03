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
                'constraint' => ['ingreso', 'salida']
            ],
            'ruta_imagen' => [
                'type' => 'VARCHAR',
                'constraint' => 255
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

        $this->forge->createTable('dispositivo_imagenes');
    }

    public function down()
    {
        $this->forge->dropTable('dispositivo_imagenes');
    }
}
