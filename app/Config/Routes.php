<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomePageController::index');

$routes->group('auth', function (RouteCollection $routes) {
    $routes->get('login', 'Auth\LoginController::login');
    $routes->post('login/process', 'Auth\LoginController::loginProcess');
    $routes->get('logout', 'Auth\LoginController::logout');
});

//Ruta publica para seguimiento de órdenes
$routes->get('consulta/orden/(:segment)', 'ConsultaController::verOrden/$1');
$routes->get('consulta/mis-ordenes', 'ConsultaController::buscarPorCedula');

$routes->group('admin', ['filter' => 'auth'], function (RouteCollection $routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    $routes->group('usuarios', function (RouteCollection $routes) {
        $routes->get('', 'Admin\UsuariosController::index');
        $routes->post('crear', 'Admin\UsuariosController::crear');
        $routes->post('editar', 'Admin\UsuariosController::editar');
        $routes->post('eliminar', 'Admin\UsuariosController::eliminar');
    });

    $routes->group('clientes', function (RouteCollection $routes) {
        $routes->get('', 'Admin\ClientesController::index');
        $routes->get('ver/(:num)', 'Admin\ClientesController::ver/$1');
        $routes->post('crear', 'Admin\ClientesController::crear');
        $routes->post('editar', 'Admin\ClientesController::editar');
        $routes->post('eliminar', 'Admin\ClientesController::eliminar');
        $routes->post('buscarCedula', 'Admin\ClientesController::buscarCedula');
        $routes->post('crear-js', 'Admin\ClientesController::crearJs');
        $routes->post('actualizar-js', 'Admin\ClientesController::actualizarJs');
    });

    $routes->group('ordenes', function (RouteCollection $routes) {
        $routes->get('', 'Admin\OrdenController::index');
        $routes->get('crear', 'Admin\OrdenController::crear');
        $routes->post('crear', 'Admin\OrdenController::guardar');
        $routes->post('guardar', 'Admin\OrdenController::guardar');
        $routes->get('imprimir/(:num)', 'Admin\OrdenController::imprimir/$1');
        $routes->get('imprimir/(:num)/(:any)', 'Admin\OrdenController::imprimir/$1/$2');
        $routes->get('entregar/(:num)', 'Admin\OrdenController::entregar/$1');
        $routes->get('dispositivos/(:num)', 'Admin\OrdenController::getDispositivosOrden/$1');
    });

    $routes->get('dispositivos/detalle/(:num)', 'Admin\DispositivoController::detalleDispositivo/$1');

    $routes->post('dispositivos/reparacion/iniciar', 'Admin\DispositivoController::iniciarReparacion');
    $routes->post('dispositivos/reparacion/finalizar', 'Admin\DispositivoController::finalizarReparacion');
    $routes->post('dispositivos/entregar', 'Admin\DispositivoController::entregarDispositivo');
    $routes->post('dispositivos/asignar-tecnico', 'Admin\DispositivoController::asignarTecnico');

    $routes->group('checklist', function ($routes) {
        $routes->get('listar', 'Admin\ChecklistController::listar'); // Para fetchTopChecks
        $routes->get('buscar', 'Admin\ChecklistController::buscar'); // Para searchChecks
        $routes->post('crear', 'Admin\ChecklistController::crear');  // Para createAndSelectCheck
    });

    $routes->group('tipos-dispositivos', function ($routes) {
        $routes->get('', 'Admin\TiposDispositivosController::index');
        $routes->post('crear', 'Admin\TiposDispositivosController::crear');
        $routes->post('editar', 'Admin\TiposDispositivosController::editar');
        $routes->post('eliminar', 'Admin\TiposDispositivosController::eliminar');
    });

    $routes->group('terminos-condiciones', function ($routes) {
        $routes->get('', 'Admin\TerminosCondicionesController::index');
        $routes->post('crear', 'Admin\TerminosCondicionesController::crear');
        $routes->post('editar', 'Admin\TerminosCondicionesController::editar');
        $routes->post('eliminar', 'Admin\TerminosCondicionesController::eliminar');
    });

    $routes->group('configuracion', function (RouteCollection $routes) {
        $routes->get('', 'Admin\ConfiguracionController::index');
        $routes->post('guardar', 'Admin\ConfiguracionController::guardar');
    });

    $routes->group('prioridades', function ($routes) {
        $routes->get('/', 'Admin\PrioridadController::index');
        $routes->post('crear', 'Admin\PrioridadController::crear');
        $routes->post('editar', 'Admin\PrioridadController::editar');
        $routes->post('eliminar', 'Admin\PrioridadController::eliminar');
    });

    $routes->group('problemas', function ($routes) {
        $routes->get('/', 'Admin\ProblemasController::index');
        $routes->post('crear', 'Admin\ProblemasController::crear');
        $routes->post('editar', 'Admin\ProblemasController::editar');
        $routes->post('eliminar', 'Admin\ProblemasController::eliminar');

        // Precios por modelo (AJAX)
        $routes->get('precios-modelo/(:num)', 'Admin\ProblemasController::getPreciosModelo/$1');
        $routes->post('guardar-precio-modelo', 'Admin\ProblemasController::guardarPrecioModelo');
        $routes->post('eliminar-precio-modelo', 'Admin\ProblemasController::eliminarPrecioModelo');

        // Cascading selects (AJAX)
        $routes->get('marcas-por-tipo/(:num)', 'Admin\ProblemasController::getMarcasPorTipo/$1');
        $routes->get('modelos-por-marca/(:num)', 'Admin\ProblemasController::getModelosPorMarca/$1');
    });

    $routes->group('horarios-atencion', function ($routes) {
        $routes->get('/', 'Admin\HorarioAtencionController::index');
        $routes->post('crear', 'Admin\HorarioAtencionController::crear');
        $routes->post('editar', 'Admin\HorarioAtencionController::editar');
        $routes->post('eliminar', 'Admin\HorarioAtencionController::eliminar');
    });

    $routes->group('historial', function ($routes) {
        // Listado principal (Bitácora)
        $routes->get('/', 'Admin\HistorialController::index');

        // Acciones del CRUD
        $routes->post('crear', 'Admin\HistorialController::crear');   // Crear nota manual
        $routes->post('editar', 'Admin\HistorialController::editar'); // Editar comentario/visibilidad
        $routes->post('eliminar', 'Admin\HistorialController::eliminar'); // Eliminar registro
    });

    $routes->group('repuestos', function ($routes) {
        $routes->get('', 'Admin\RepuestosController::index');
        $routes->post('crear', 'Admin\RepuestosController::crear');
        $routes->post('editar', 'Admin\RepuestosController::editar');
        $routes->post('eliminar', 'Admin\RepuestosController::eliminar');
        $routes->get('buscar', 'Admin\RepuestosController::buscar'); // Para fetch/autocomplete
    });

    // Dispositivos por técnico
    $routes->get('dispositivos', 'Admin\DispositivoController::index');
    $routes->get('dispositivos/mis-reparaciones', 'Admin\DispositivoController::misReparaciones');
    $routes->get('dispositivos/ver-tecnico/(:num)', 'Admin\DispositivoController::verTecnico/$1');
    $routes->get('dispositivos/ver-tecnico/(:num)/comisiones', 'Admin\DispositivoController::comisionesPorMes/$1');
    $routes->get('dispositivos/ver-tecnico/(:num)/ingresos', 'Admin\DispositivoController::dispositivosPorMes/$1');


    $routes->group('pagos-tecnicos', function ($routes) {
        // Listado principal (Bitácora)
        $routes->get('/', 'Admin\PagosTecnicosController::index');

        $routes->post('validar', 'Admin\PagosTecnicosController::validar');
        $routes->post('marcar-pagado', 'Admin\PagosTecnicosController::marcarPagado');
        $routes->post('validar-lote', 'Admin\PagosTecnicosController::validarLote');
        $routes->get('tecnico/(:num)', 'Admin\PagosTecnicosController::historialTecnico/$1');
    });

});

$routes->group('recepcionista', function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Recepcionista\DashboardController::index');

    // Órdenes de Trabajo
    $routes->get('ordenes', 'Recepcionista\OrdenController::index');
    $routes->get('ordenes/crear', 'Recepcionista\OrdenController::crear');
    $routes->post('ordenes/guardar', 'Recepcionista\OrdenController::guardar');
    $routes->get('ordenes/ver/(:num)', 'Recepcionista\OrdenController::ver/$1');
    $routes->get('ordenes/imprimir/(:num)', 'Recepcionista\OrdenController::imprimir/$1');
    $routes->post('ordenes/entregar/(:num)', 'Recepcionista\OrdenController::entregar/$1');

    // Dispositivos
    $routes->get('dispositivos/ver/(:num)', 'Recepcionista\DispositivoController::ver/$1');
});


// Grupo de rutas para técnicos
$routes->group('tecnico', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Tecnico\DashboardController::index');

    // Clientes
    $routes->group('clientes', function ($routes) {
        $routes->get('', 'Tecnico\ClientesController::index');
        $routes->get('ver/(:num)', 'Tecnico\ClientesController::ver/$1');
        $routes->post('crear', 'Tecnico\ClientesController::crear');
        $routes->post('editar', 'Tecnico\ClientesController::editar');
        $routes->post('eliminar', 'Tecnico\ClientesController::eliminar');
        $routes->post('buscarCedula', 'Tecnico\ClientesController::buscarCedula');
        $routes->post('crear-js', 'Tecnico\ClientesController::crearJs');
        $routes->post('actualizar-js', 'Tecnico\ClientesController::actualizarJs');
    });

    // Órdenes
    $routes->group('ordenes', function ($routes) {
        $routes->get('crear', 'Tecnico\OrdenController::crear');
        $routes->post('guardar', 'Tecnico\OrdenController::guardar');
        $routes->get('imprimir/(:num)', 'Tecnico\OrdenController::imprimir/$1');
        $routes->get('imprimir/(:num)/(:any)', 'Tecnico\OrdenController::imprimir/$1/$2');
    });

    // Dispositivos
    $routes->get('dispositivos/asignados', 'Tecnico\DispositivoController::asignados');
    $routes->get('dispositivos/mis-reparaciones', 'Tecnico\DispositivoController::misReparaciones');
    $routes->get('dispositivos/pool', 'Tecnico\DispositivoController::pool');
    $routes->get('dispositivos/detalle/(:num)', 'Tecnico\DispositivoController::detalle/$1');

    // Acciones de reparación
    $routes->post('dispositivos/reparacion/iniciar', 'Tecnico\DispositivoController::iniciarReparacion');
    $routes->post('dispositivos/reparacion/finalizar', 'Tecnico\DispositivoController::finalizarReparacion');
    $routes->post('dispositivos/reparacion/entregar', 'Tecnico\DispositivoController::entregarDispositivo');

    // Ingresos
    $routes->get('ingresos', 'Tecnico\IngresosController::index');
});
//Rutas de consulta global con autenticación
//TODO: AGREGAR FILTRO DE AUTENTICACIÓN
$routes->group('global', ['filter' => 'auth'], function ($routes) {

    // Obtener marcas y modelos (AJAX)
    $routes->get('get-marcas-por-tipo/(:num)', 'GlobalController::getMarcasPorTipoGlobal/$1');
    $routes->get('get-modelos-por-marca/(:num)', 'GlobalController::getModelosPorMarcaGlobal/$1');
    //Crear marcas y modelos
    $routes->post('crear-marca', 'GlobalController::crearMarca');
    $routes->post('crear-modelo', 'GlobalController::crearModelo');
    //Buscar marcas y modelos
    $routes->get('buscar-marcas', 'GlobalController::buscarMarcas');
    $routes->get('buscar-modelos', 'GlobalController::buscarModelos');


    // Rutas para Accesorios
    $routes->get('buscar-accesorios', 'GlobalController::buscarAccesorios');
    $routes->post('crear-accesorio', 'GlobalController::crearAccesorio');

    // Rutas para detalles
    $routes->get('buscar-detalles', 'GlobalController::buscarDetalles');
    $routes->post('crear-detalles', 'GlobalController::crearDetalles');

    // Problemas comunes
    $routes->get('buscar-problemas-comunes', 'GlobalController::buscarProblemasComunes');
    $routes->post('crear-problema-comun', 'GlobalController::crearProblemasComun');
    $routes->post('registrar-uso-problema', 'GlobalController::registrarUsoProblema');

    $routes->get('dispositivos/(:num)', 'GlobalController::obtenerDispositivos/$1');
});
