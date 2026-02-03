<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearChecklistItems extends Migration
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
            'tipo_dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL = aplica a todos los tipos',
            ],
            'categoria' => [
                'type' => 'ENUM',
                'constraint' => ['fisico', 'funcional', 'estetico', 'accesorio'],
                'default' => 'fisico',
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'es_critico' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Si afecta funcionalidad principal',
            ],
            'requiere_foto' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->createTable('checklist_items');

        $this->db->query('CREATE INDEX idx_tipo_categoria ON checklist_items(tipo_dispositivo_id, categoria)');

        // Insertar items por defecto
        $this->insertDefaultItems();
    }

    private function insertDefaultItems()
    {
        $items = [
            // Items generales (todos los dispositivos)
            ['tipo_dispositivo_id' => null, 'categoria' => 'funcional', 'nombre' => 'Enciende correctamente', 'es_critico' => true],
            ['tipo_dispositivo_id' => null, 'categoria' => 'funcional', 'nombre' => 'Carga batería', 'es_critico' => true],
            ['tipo_dispositivo_id' => null, 'categoria' => 'fisico', 'nombre' => 'Pantalla intacta', 'requiere_foto' => true],
            ['tipo_dispositivo_id' => null, 'categoria' => 'fisico', 'nombre' => 'Carcasa en buen estado'],
            ['tipo_dispositivo_id' => null, 'categoria' => 'fisico', 'nombre' => 'Sin golpes visibles', 'requiere_foto' => true],
            ['tipo_dispositivo_id' => null, 'categoria' => 'fisico', 'nombre' => 'Sin señales de humedad', 'es_critico' => true],
        ];

        foreach ($items as $item) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('checklist_items')->insert($item);
        }
    }

    public function down()
    {
        $this->forge->dropTable('checklist_items');
    }
}
