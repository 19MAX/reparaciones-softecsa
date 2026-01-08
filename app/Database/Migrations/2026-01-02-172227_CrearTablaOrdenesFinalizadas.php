<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaOrdenesFinalizadas extends Migration
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
                'constraint' => 50
            ],
            'cliente_json' => [
                'type' => 'LONGTEXT'
            ],
            'empresa_json' => [
                'type' => 'LONGTEXT'
            ],
            'dispositivos_json' => [
                'type' => 'LONGTEXT'
            ],
            'checklist_json' => [
                'type' => 'LONGTEXT'
            ],
            'imagenes_json' => [
                'type' => 'LONGTEXT'
            ],
            'terminos_texto' => [
                'type' => 'LONGTEXT'
            ],
            'urgencia_json' => [
                'type' => 'LONGTEXT',
                'null' => true
            ],
            'mano_obra' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'valor_repuestos' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'ganancia_tecnico' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'fecha_finalizacion' => [
                'type' => 'DATETIME'
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('ordenes_finalizadas');
    }

    public function down()
    {
        $this->forge->dropTable('ordenes_finalizadas');
    }
}
