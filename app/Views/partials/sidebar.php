<div class="sidebar sidebar-style-2" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="index.html" class="logo">
                <img src="<?= base_url('assets') ?>/images/logo.png" alt="navbar brand" class="navbar-brand"
                    height="20">
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <?php $userRole = session()->get('role'); ?>

            <ul class="nav nav-secondary">

                <?php if ($userRole === 'admin'): ?>
                    <!-- SIDEBAR ADMIN -->
                    <li class="nav-item <?= strpos(current_url(), 'admin/dashboard') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/dashboard') ?>">
                            <i class="fas fa-home"></i>
                            <p>Inicio</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'admin/usuarios') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/usuarios') ?>">
                            <i class="fas fa-users-cog"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'admin/clientes') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/clientes') ?>">
                            <i class="fas fa-users"></i>
                            <p>Clientes</p>
                        </a>
                    </li>

                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Manejo</h4>
                    </li>

                    <li class="nav-item <?= sidebar_class(sidebar_module_active('configuracion_inicial'), 'active submenu') ?>">
                        <a data-bs-toggle="collapse" href="#forms">
                            <i class="fas fa-pen-square"></i>
                            <p>Configuración Inicial</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse  <?= sidebar_class(sidebar_module_active('configuracion_inicial'), 'show') ?>"
                            id="forms">
                            <ul class="nav nav-collapse">

                                <li class="<?= sidebar_class(sidebar_item_active('UrgenciaController'), 'active') ?>">
                                    <a href="<?= base_url('admin/urgencias') ?>">
                                        <span class="sub-item">Prioridad</span>
                                    </a>
                                </li>

                                <li
                                    class="<?= sidebar_class(sidebar_item_active('TiposDispositivosController'), 'active') ?>">
                                    <a href="<?= base_url('admin/tipos-dispositivos') ?>">
                                        <span class="sub-item">Tipos de Dispositivos</span>
                                    </a>
                                </li>

                                <li
                                    class="<?= sidebar_class(sidebar_item_active('TerminosCondicionesController'), 'active') ?>">
                                    <a href="<?= base_url('admin/terminos-condiciones') ?>">
                                        <span class="sub-item">Términos y Condiciones</span>
                                    </a>
                                </li>

                                <li class="<?= sidebar_class(sidebar_item_active('ConfiguracionController'), 'active') ?>">
                                    <a href="<?= base_url('admin/configuracion') ?>">
                                        <span class="sub-item">Configuración</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item <?= strpos(current_url(), 'admin/ordenes') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/ordenes') ?>">
                            <i class="fas fa-toolbox"></i>
                            <p>Órdenes</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'admin/dispositivos') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/dispositivos') ?>">
                            <i class="fas fa-laptop"></i>
                            <p>Dispositivos por Técnico</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'admin/historial') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('admin/historial') ?>">
                            <i class="fas fa-clock"></i>
                            <p>Historial</p>
                        </a>
                    </li>

                <?php elseif ($userRole === 'recepcionista'): ?>
                    <!-- SIDEBAR RECEPCIONISTA -->
                    <li class="nav-item <?= strpos(current_url(), 'recepcionista/dashboard') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('recepcionista/dashboard') ?>">
                            <i class="fas fa-home"></i>
                            <p>Inicio</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'recepcionista/clientes') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('recepcionista/clientes') ?>">
                            <i class="fas fa-users"></i>
                            <p>Clientes</p>
                        </a>
                    </li>

                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Manejo</h4>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'recepcionista/ordenes') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('recepcionista/ordenes') ?>">
                            <i class="fas fa-toolbox"></i>
                            <p>Órdenes</p>
                        </a>
                    </li>

                    <li
                        class="nav-item <?= strpos(current_url(), 'recepcionista/dispositivos') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('recepcionista/dispositivos') ?>">
                            <i class="fas fa-laptop"></i>
                            <p>Dispositivos</p>
                        </a>
                    </li>

                <?php elseif ($userRole === 'tecnico'): ?>
                    <!-- SIDEBAR TÉCNICO -->
                    <li class="nav-item <?= strpos(current_url(), 'tecnico/dashboard') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('tecnico/dashboard') ?>">
                            <i class="fas fa-home"></i>
                            <p>Inicio</p>
                        </a>
                    </li>

                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section">Mi Trabajo</h4>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'tecnico/dispositivos') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('tecnico/dispositivos') ?>">
                            <i class="fas fa-laptop"></i>
                            <p>Mis Dispositivos</p>
                        </a>
                    </li>

                    <li class="nav-item <?= strpos(current_url(), 'tecnico/ingresos') !== false ? 'active' : '' ?>">
                        <a href="<?= base_url('tecnico/ingresos') ?>">
                            <i class="fas fa-dollar-sign"></i>
                            <p>Mis Ingresos</p>
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</div>