<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Sidebar extends BaseConfig
{
     public static array $modules = [

         'configuracion_inicial' => [
            'controllers' => [
                'UrgenciaController',
                'TiposDispositivosController',
                'TerminosCondicionesController',
                'ConfiguracionController',
            ],
            'items' => [
                'UrgenciaController',
                'TiposDispositivosController',
                'TerminosCondicionesController',
                'ConfiguracionController',
            ],
        ],

        'usuarios' => [
            'controllers' => [
                'UsuariosController',
            ],
            'roles' => ['admin'],
        ],

        'clientes' => [
            'controllers' => [
                'ClientesController',
            ],
            'roles' => ['admin', 'tecnico'],
        ],

        'ordenes' => [
            'controllers' => [
                'OrdenController',
            ],
            'roles' => ['admin', 'tecnico'],
        ],
    ];
}
