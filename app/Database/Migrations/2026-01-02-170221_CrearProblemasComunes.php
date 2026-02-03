<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearProblemasComunes extends Migration
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
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'categoria' => [
                'type' => 'ENUM',
                'constraint' => ['hardware', 'software', 'liquido', 'usuario'],
                'default' => 'hardware',
            ],
            'descripcion_tecnica' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'solucion_sugerida' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tiempo_promedio_reparacion' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'En minutos',
            ],
            'costo_promedio' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'veces_reportado' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
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
        $this->forge->createTable('problemas_comunes');

        $this->db->query('CREATE INDEX idx_tipo_categoria ON problemas_comunes(tipo_dispositivo_id, categoria)');
        $this->db->query('CREATE INDEX idx_veces_reportado ON problemas_comunes(veces_reportado)');

        // Insertar problemas comunes
        $this->insertProblemasDefault();
    }

    private function insertProblemasDefault()
    {
        $problemas = [
            ['nombre' => 'No enciende', 'categoria' => 'hardware', 'tiempo_promedio_reparacion' => 120],
            ['nombre' => 'Pantalla rota', 'categoria' => 'hardware', 'tiempo_promedio_reparacion' => 45],
            ['nombre' => 'Batería agotada', 'categoria' => 'hardware', 'tiempo_promedio_reparacion' => 30],
            ['nombre' => 'No carga', 'categoria' => 'hardware', 'tiempo_promedio_reparacion' => 60],
            ['nombre' => 'Táctil no responde', 'categoria' => 'hardware', 'tiempo_promedio_reparacion' => 50],
            ['nombre' => 'Lento / Cuelga', 'categoria' => 'software', 'tiempo_promedio_reparacion' => 40],
            ['nombre' => 'Virus / Malware', 'categoria' => 'software', 'tiempo_promedio_reparacion' => 35],
            ['nombre' => 'Daño por líquido', 'categoria' => 'liquido', 'tiempo_promedio_reparacion' => 180],
        ];

        foreach ($problemas as $prob) {
            $prob['tipo_dispositivo_id'] = null;
            $prob['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('problemas_comunes')->insert($prob);
        }
    }

    public function down()
    {
        $this->forge->dropTable('problemas_comunes');
    }
}
