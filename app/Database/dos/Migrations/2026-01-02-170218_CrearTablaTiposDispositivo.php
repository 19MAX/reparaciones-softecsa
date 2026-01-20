<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaTiposDispositivo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre' => [ // Ej: Celular, Laptop, Impresora, Consola
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'icono' => [ // Opcional: para mostrar en el frontend (ej: 'fa-mobile')
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tipos_dispositivo');
    }

    public function down()
    {
        $this->forge->dropTable('tipos_dispositivo');
    }
}
