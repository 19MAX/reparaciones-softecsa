<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearPoliticasRevision extends Migration
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
            'cobra_revision_si_rechaza_cliente' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'cobra_revision_si_no_hay_diagnostico' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'cobra_revision_si_reparacion_exitosa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'valor_revision_base' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'es_politica_activa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'descripcion_terminos' => [
                'type' => 'TEXT',
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
        $this->forge->createTable('politicas_revision');

        // Insertar política por defecto
        $this->db->table('politicas_revision')->insert([
            'nombre' => 'Política Estándar',
            'cobra_revision_si_rechaza_cliente' => true,
            'cobra_revision_si_no_hay_diagnostico' => false,
            'cobra_revision_si_reparacion_exitosa' => false,
            'valor_revision_base' => 15.00,
            'es_politica_activa' => true,
            'descripcion_terminos' => 'Si no autoriza la reparación después del diagnóstico, se cobrará $15.00 por revisión técnica. Si no encontramos el problema, no se cobra.',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('politicas_revision');
    }
}
