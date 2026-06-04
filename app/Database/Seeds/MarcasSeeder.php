<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MarcasSeeder extends Seeder
{
public function run()
    {
        // Obtenemos los IDs de los tipos de dispositivo dinámicamente
        $tipos = $this->db->table('tipos_dispositivo')
                          ->select('id, nombre')
                          ->get()
                          ->getResultArray();

        // Mapeamos nombre => id
        $tiposMap = array_column($tipos, 'id', 'nombre');

        $now = date('Y-m-d H:i:s');

        $data = [];

        // ─── Impresora ───────────────────────────────────────────
        $idImpresora = $tiposMap['Impresora'] ?? null;
        if ($idImpresora) {
            $marcasImpresora = ['HP', 'Epson', 'Canon', 'Brother', 'Lexmark'];
            foreach ($marcasImpresora as $marca) {
                $data[] = [
                    'tipo_dispositivo_id' => $idImpresora,
                    'nombre'              => $marca,
                    'activo'              => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        // ─── Laptop ──────────────────────────────────────────────
        $idLaptop = $tiposMap['Laptop'] ?? null;
        if ($idLaptop) {
            $marcasLaptop = ['Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Apple'];
            foreach ($marcasLaptop as $marca) {
                $data[] = [
                    'tipo_dispositivo_id' => $idLaptop,
                    'nombre'              => $marca,
                    'activo'              => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        // ─── Celular ─────────────────────────────────────────────
        $idCelular = $tiposMap['Celular'] ?? null;
        if ($idCelular) {
            $marcasCelular = ['Samsung', 'Apple', 'Huawei', 'Xiaomi', 'Motorola'];
            foreach ($marcasCelular as $marca) {
                $data[] = [
                    'tipo_dispositivo_id' => $idCelular,
                    'nombre'              => $marca,
                    'activo'              => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        // ─── Cámara ──────────────────────────────────────────────
        $idCamara = $tiposMap['Cámara'] ?? null;
        if ($idCamara) {
            $marcasCamara = ['Hikvision', 'Dahua', 'Sony', 'Canon', 'Axis'];
            foreach ($marcasCamara as $marca) {
                $data[] = [
                    'tipo_dispositivo_id' => $idCamara,
                    'nombre'              => $marca,
                    'activo'              => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        // ─── CPU ─────────────────────────────────────────────────
        $idCpu = $tiposMap['CPU'] ?? null;
        if ($idCpu) {
            $marcasCpu = ['Genérico', 'Dell', 'HP', 'Lenovo', 'Asus'];
            foreach ($marcasCpu as $marca) {
                $data[] = [
                    'tipo_dispositivo_id' => $idCpu,
                    'nombre'              => $marca,
                    'activo'              => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
        }

        if (!empty($data)) {
            $this->db->table('marcas')->insertBatch($data);
        }
    }
}
