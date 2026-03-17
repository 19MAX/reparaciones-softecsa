<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HorariosAtencionSeeder extends Seeder
{
        public function run()
    {
        $data = [];

        // Domingo (cerrado)
        $data[] = [
            'dia_semana' => 0,
            'hora_apertura' => '00:00:00',
            'hora_cierre' => '00:00:00',
            'abierto' => 0,
        ];

        // Lunes a viernes (1 - 5)
        for ($i = 1; $i <= 5; $i++) {
            $data[] = [
                'dia_semana' => $i,
                'hora_apertura' => '08:00:00',
                'hora_cierre' => '19:00:00',
                'abierto' => 1,
            ];
        }

        // Sábado (6)
        $data[] = [
            'dia_semana' => 6,
            'hora_apertura' => '08:00:00',
            'hora_cierre' => '13:00:00',
            'abierto' => 1,
        ];

        // Insertar en la tabla
        $this->db->table('horarios_atencion')->insertBatch($data);
    }
}
