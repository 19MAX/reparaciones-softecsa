<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ModelosSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $marcas = $this->db->table('marcas m')
                           ->select('m.id, m.nombre as marca, t.nombre as tipo')
                           ->join('tipos_dispositivo t', 't.id = m.tipo_dispositivo_id')
                           ->get()
                           ->getResultArray();

        $marcasMap = [];
        foreach ($marcas as $m) {
            $key = $m['tipo'] . '|' . $m['marca'];
            $marcasMap[$key] = $m['id'];
        }

        $modelosPorMarca = [

            // ── IMPRESORAS ──────────────────────────────────────
            'Impresora|HP'      => ['M404dn', 'M428fdw', 'P1102w'],
            'Impresora|Epson'   => ['L4160', 'L3250', 'L6270'],
            'Impresora|Canon'   => ['G3160', 'MF264dw', 'E3170'],
            'Impresora|Brother' => ['L2350DW', 'L2710DW', 'L2550DW'],
            'Impresora|Lexmark' => ['B2236dw', 'MB2236adw', 'MC2325adw'],

            // ── LAPTOPS ─────────────────────────────────────────
            'Laptop|Dell'   => ['Inspiron 15', 'Latitude 5540', 'XPS 13'],
            'Laptop|HP'     => ['Pavilion 15', 'EliteBook 840', 'ProBook 450'],
            'Laptop|Lenovo' => ['IdeaPad 3', 'ThinkPad E14', 'Legion 5'],
            'Laptop|Asus'   => ['VivoBook 15', 'ZenBook 14', 'ROG G15'],
            'Laptop|Acer'   => ['Aspire 5', 'Swift 3', 'Nitro 5'],
            'Laptop|Apple'  => ['MacBook Air M1', 'MacBook Air M2', 'MacBook Pro M3'],

            // ── CELULARES ────────────────────────────────────────
            'Celular|Samsung'  => ['A13', 'A54', 'S23'],
            'Celular|Apple'    => ['iPhone 13', 'iPhone 14', 'iPhone 15'],
            'Celular|Huawei'   => ['P30 Lite', 'Y9s', 'Nova 11'],
            'Celular|Xiaomi'   => ['Redmi 12', 'Poco X3', 'Poco X5'],
            'Celular|Motorola' => ['Moto E13', 'Moto G84', 'Edge 40'],

            // ── CÁMARAS ─────────────────────────────────────────
            'Cámara|Hikvision' => ['DS-2CD2143', 'DS-2CD2T47', 'DS-2DE4425'],
            'Cámara|Dahua'     => ['IPC-HDW2831', 'IPC-HFW2849', 'SD49425'],
            'Cámara|Sony'      => ['Alpha a6400', 'ZV-E10', 'FX30'],
            'Cámara|Canon'     => ['EOS R50', 'EOS SL3', 'PowerShot V10'],
            'Cámara|Axis'      => ['P3245-V', 'Q6135-LE', 'M3106-L'],

            // ── CPU ─────────────────────────────────────────────
            'CPU|Genérico' => ['Básico', 'Medio', 'Alto Rendimiento'],
            'CPU|Dell'     => ['OptiPlex 3000', 'OptiPlex 7000', 'Precision 3660'],
            'CPU|HP'       => ['ProDesk 400', 'EliteDesk 800', 'Z2 Tower'],
            'CPU|Lenovo'   => ['ThinkCentre M70q', 'ThinkCentre M90t', 'IdeaCentre 5'],
            'CPU|Asus'     => ['ExpertCenter D500', 'ProArt PD500', 'Mini PC PN53'],
        ];

        $data = [];

        foreach ($modelosPorMarca as $key => $modelos) {
            $marcaId = $marcasMap[$key] ?? null;

            if (!$marcaId) {
                continue;
            }

            foreach ($modelos as $nombreModelo) {
                $data[] = [
                    'marca_id'   => $marcaId,
                    'nombre'     => $nombreModelo,
                    'activo'     => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($data)) {
            $this->db->table('modelos')->insertBatch($data);
        }
    }
}
