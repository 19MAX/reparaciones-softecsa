<?php

namespace App\Database\Seeds;

use App\Models\UsuarioModel;
use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $model = new UsuarioModel();
        $adminPassword = env('SEEDER_ADMIN_PASSWORD');

        $data = [
            'cedula' => '0000000000',
            'nombres' => 'Admin',
            'apellidos' => 'Sistema',
            'password' => $adminPassword,
            'role' => 'admin',
            'estado' => 'activo'
        ];

        $model->insert($data);
    }
}
