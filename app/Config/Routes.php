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

$routes->group('admin', function (RouteCollection $routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    $routes->group('usuarios', function (RouteCollection $routes) {
        $routes->get('', 'Admin\UsuariosController::index');
        $routes->post('crear', 'Admin\UsuariosController::crear');
        $routes->post('editar', 'Admin\UsuariosController::editar');
        $routes->post('eliminar', 'Admin\UsuariosController::eliminar');
    });

    $routes->group('clientes', function (RouteCollection $routes) {
        $routes->get('', 'Admin\ClientesController::index');
        $routes->post('crear', 'Admin\ClientesController::crear');
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
        $routes->get('entregar/(:num)', 'Admin\OrdenController::entregar/$1');
    });
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

    $routes->group('urgencias', function ($routes) {
        $routes->get('/', 'Admin\UrgenciaController::index');
        $routes->post('crear', 'Admin\UrgenciaController::crear');
        $routes->post('editar', 'Admin\UrgenciaController::editar');
        $routes->post('eliminar', 'Admin\UrgenciaController::eliminar');
    });

    $routes->group('historial', function ($routes) {
        // Listado principal (Bitácora)
        $routes->get('/', 'Admin\HistorialController::index');

        // Acciones del CRUD
        $routes->post('crear', 'Admin\HistorialController::crear');   // Crear nota manual
        $routes->post('editar', 'Admin\HistorialController::editar'); // Editar comentario/visibilidad
        $routes->post('eliminar', 'Admin\HistorialController::eliminar'); // Eliminar registro
    });
    // Dispositivos por técnico
    $routes->get('dispositivos', 'Admin\DispositivoController::index');
    $routes->get('dispositivos/ver-tecnico/(:num)', 'Admin\DispositivoController::verTecnico/$1');

    $routes->get('dispositivos/detalle/(:num)', 'Admin\DispositivoController::detalle/$1');
    $routes->get('dispositivos/finalizar/(:num)', 'Admin\FinalizacionController::finalizar/$1');
    $routes->post('dispositivos/procesar-finalizacion', 'Admin\FinalizacionController::procesarFinalizacion');

});

$routes->group('recepcionista', function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Recepcionista\DashboardController::index');

    $routes->get('dashboardPrueba', 'RecepcionistaController::index');


    // Crear Orden
    $routes->get('crear-orden', 'RecepcionistaController::crearOrden');
    $routes->post('guardar-orden', 'RecepcionistaController::guardarOrden');


    // Cliente (AJAX)
    $routes->post('buscar-cliente', 'RecepcionistaController::buscarCliente');
    $routes->post('crear-cliente', 'RecepcionistaController::crearCliente');
    $routes->post('actualizar-cliente', 'RecepcionistaController::actualizarCliente');

    // Gestión de garantías
    $routes->get('iniciar-reclamo-garantia', 'RecepcionistaController::iniciarReclamoGarantia');

    // Consultar estado
    $routes->get('consultar-estado', 'RecepcionistaController::consultarEstado');
    $routes->get('orden/(:num)', 'RecepcionistaController::verOrden/$1');

    // Autorización cliente
    $routes->post('registrar-autorizacion', 'RecepcionistaController::registrarAutorizacion');

    // Reclamos de garantía
    $routes->get('reclamo-garantia', 'RecepcionistaController::iniciarReclamoGarantia');
    $routes->post('buscar-dispositivo-garantia', 'RecepcionistaController::buscarDispositivoGarantia');
    $routes->post('guardar-reclamo-garantia', 'RecepcionistaController::guardarReclamoGarantia');
    $routes->post('solicitar-excepcion-garantia', 'RecepcionistaController::solicitarExcepcionGarantia');

    // Entregas
    $routes->get('dispositivos-para-entregar', 'RecepcionistaController::dispositivosParaEntregar');
    $routes->get('entregar/(:num)', 'RecepcionistaController::dispositivosParaEntregar'); // Vista de entrega específica
    $routes->post('procesar-entrega', 'RecepcionistaController::procesarEntrega');

    // Imprimir
    $routes->get('imprimir-orden/(:num)', 'RecepcionistaController::imprimirOrden/$1');

    // Órdenes pendientes (opcional)
    $routes->get('ordenes-pendientes', 'RecepcionistaController::ordenesPendientes');

    // Listado de clientes (opcional)
    $routes->get('clientes', 'RecepcionistaController::listarClientes');

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

//Rutas de consulta global con autenticación
//TODO: AGREGAR FILTRO DE AUTENTICACIÓN
$routes->group('global', function ($routes) {

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

    // Rutas para Checklist
    $routes->get('buscar-checklist', 'GlobalController::buscarChecklist');
    $routes->post('crear-checklist-item', 'GlobalController::crearChecklistItem');

    // Problemas comunes
    $routes->get('buscar-problemas-comunes', 'GlobalController::buscarProblemasComunes');
    $routes->post('crear-problema-comun', 'GlobalController::crearProblemasComun');
    $routes->post('registrar-uso-problema', 'GlobalController::registrarUsoProblema');

    $routes->get('dispositivos/(:num)', 'GlobalController::obtenerDispositivos/$1');
});


// Grupo de rutas para técnicos
$routes->group('tecnico', function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Tecnico\DashboardController::index');

    //Dispositivos
    $routes->get('dispositivos', 'Tecnico\DispositivoController::index');

    $routes->get('dispositivos/trabajar/(:num)', 'Tecnico\DispositivoController::trabajar/$1');

    $routes->post('dispositivos/iniciar-diagnostico', 'Tecnico\DispositivoController::iniciarDiagnostico');
    $routes->post('dispositivos/agregar-problema', 'Tecnico\DispositivoController::agregarProblema');
    $routes->post('dispositivos/registrar-garantia', 'Tecnico\DispositivoController::registrarGarantia');
    //Generar presupuesto
    $routes->post('dispositivos/generar-presupuesto', 'Tecnico\DispositivoController::generarPresupuesto');
    //Actualizar problema
    $routes->post('dispositivos/actualizar-problema', 'Tecnico\DispositivoController::actualizarProblema');
    //Guardar diagnóstico
    $routes->post('dispositivos/guardar-diagnostico', 'Tecnico\DispositivoController::guardarDiagnostico');
    //Ver dispositivo


    $routes->get('dispositivos/ver/(:num)', 'Tecnico\DispositivoController::ver/$1');
    $routes->post('dispositivos/actualizarEstado', 'Tecnico\DispositivoController::actualizarEstado');

    // Ingresos
    $routes->get('ingresos', 'Tecnico\IngresosController::index');
});
