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
            'cedula' => '0291578400',
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'password' => $adminPassword,
            'rol' => 'admin',
            'activo' => 1
        ];

        $model->insert($data);
    }
}
