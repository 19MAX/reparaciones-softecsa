<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TiposDispositivoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre'      => 'Impresora',
                'descripcion' => 'Dispositivos de impresión de documentos e imágenes, incluye impresoras de inyección de tinta, láser y multifuncionales.',
                'activo'      => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'      => 'Laptop',
                'descripcion' => 'Computadoras portátiles con pantalla integrada, teclado y batería recargable para uso móvil.',
                'activo'      => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'      => 'Celular',
                'descripcion' => 'Teléfonos inteligentes y dispositivos móviles de comunicación con acceso a internet y aplicaciones.',
                'activo'      => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'      => 'Cámara',
                'descripcion' => 'Dispositivos de captura fotográfica y de video, incluye cámaras de seguridad, digitales y de videoconferencia.',
                'activo'      => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nombre'      => 'CPU',
                'descripcion' => 'Unidades centrales de procesamiento o torres de escritorio, computadoras de uso fijo en oficina o escritorio.',
                'activo'      => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('tipos_dispositivo')->insertBatch($data);
    }
}
