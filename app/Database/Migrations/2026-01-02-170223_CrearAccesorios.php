<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearAccesorios extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL = aplica a todos',
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addForeignKey('tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('accesorios');

        // Insertar accesorios comunes
        $accesorios = [
            ['nombre' => 'Cargador', 'tipo_dispositivo_id' => null],
            ['nombre' => 'Cable USB', 'tipo_dispositivo_id' => null],
            ['nombre' => 'Funda', 'tipo_dispositivo_id' => null],
            ['nombre' => 'Audífonos', 'tipo_dispositivo_id' => null],
            ['nombre' => 'Tarjeta SD', 'tipo_dispositivo_id' => null],
            ['nombre' => 'SIM card', 'tipo_dispositivo_id' => null],
        ];

        foreach ($accesorios as $acc) {
            $acc['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('accesorios')->insert($acc);
        }
    }

    public function down()
    {
        $this->forge->dropTable('accesorios');
    }
}
