<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTiposGarantia extends Migration
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
                'constraint' => 150,
            ],
            'dias_garantia' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => '0 = sin garantía',
            ],
            'descripcion_tecnica' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'descripcion_cliente' => [
                'type' => 'TEXT',
            ],
            'aplica_para_problema_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'aplica_para_tipo_dispositivo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'exclusiones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'requiere_condiciones_especiales' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'condiciones_especiales' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('aplica_para_problema_id', 'problemas_comunes', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('aplica_para_tipo_dispositivo_id', 'tipos_dispositivo', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('tipos_garantia');

        // Insertar tipos por defecto
        $tipos = [
            [
                'nombre' => 'Sin garantía',
                'dias_garantia' => 0,
                'descripcion_cliente' => 'Por la naturaleza de esta reparación, no se ofrece garantía.',
                'exclusiones' => 'N/A',
            ],
            [
                'nombre' => 'Garantía estándar - 30 días',
                'dias_garantia' => 30,
                'descripcion_cliente' => '30 días de garantía por defectos de fabricación del repuesto.',
                'exclusiones' => 'No cubre: daños físicos posteriores, contacto con líquidos, uso indebido.',
            ],
            [
                'nombre' => 'Garantía extendida - 60 días',
                'dias_garantia' => 60,
                'descripcion_cliente' => '60 días de garantía extendida.',
                'exclusiones' => 'No cubre: daños físicos posteriores, contacto con líquidos.',
            ],
        ];

        foreach ($tipos as $tipo) {
            $tipo['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('tipos_garantia')->insert($tipo);
        }
    }

    public function down()
    {
        $this->forge->dropTable('tipos_garantia');
    }
}
