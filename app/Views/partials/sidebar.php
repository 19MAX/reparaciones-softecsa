<div class="sidebar sidebar-style-2" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">

            <a href="index.html" class="logo">
                <img src="<?= base_url('assets') ?>/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand"
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
            <ul class="nav nav-secondary">

                <li class="nav-item  <?= current_url() == base_url('admin/dashboard') ? 'active' : '' ?>">
                    <a href="<?= base_url(relativePath: 'admin/dashboard') ?>">
                        <i class="fas fa-home"></i>
                        <p>Inicio</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-item  <?= current_url() == base_url('admin/usuarios') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/usuarios') ?>">
                        <i class="fas fa-users-cog"></i>
                        <p>Usuarios</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-item  <?= current_url() == base_url('admin/clientes') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/clientes') ?>">
                        <i class="fas fa-users"></i>
                        <p>Clientes</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Manejo</h4>
                </li>


                <li class="nav-item  <?= current_url() == base_url('admin/ordenes') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/ordenes') ?>">
                        <i class="fas fa-toolbox"></i>
                        <p>Ordenes</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>

                <li class="nav-item  <?= current_url() == base_url('admin/historial') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/historial') ?>">
                        <i class="fas fa-clock"></i>
                        <p>Historial</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-item  <?= current_url() == base_url('admin/urgencias') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/urgencias') ?>">
                        <i class="fas fa-bolt"></i>
                        <p>Urgencias</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-item  <?= current_url() == base_url('admin/tipos-dispositivos') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/tipos-dispositivos') ?>">
                        <i class="fas fa-laptop"></i>
                        <p>Tipos de dispositivos</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
                <li class="nav-item  <?= current_url() == base_url('admin/terminos-condiciones') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/terminos-condiciones') ?>">
                        <i class="fas fa-gavel"></i>
                        <p>Términos y condiciones</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#submenu">
                        <i class="fas fa-toolbox"></i>
                        <p>Ordene de reparación</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="submenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="../demo1/index.html">
                                    <span class="sub-item">Todas</span>
                                </a>
                            </li>
                            <li>
                                <a data-bs-toggle="collapse" href="#subnav1">
                                    <span class="sub-item">Por estado</span>
                                    <span class="caret"></span>
                                </a>
                                <div class="collapse" id="subnav1">
                                    <ul class="nav nav-collapse subnav">
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Ingresados</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Pendientes</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <span class="sub-item">Finalizado</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <a href="components/avatars.html">
                                    <span class="sub-item">Crear</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#termsSubmenu">
                        <i class="fas fa-th-list"></i>
                        <p>Términos y condiciones</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="termsSubmenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="../demo1/index.html">
                                    <span class="sub-item">Todas</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item  <?= current_url() == base_url('admin/configuracion') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/configuracion') ?>">
                        <i class="fas fa-cog"></i>
                        <p>Configuración</p>
                        <!-- <span class="badge badge-secondary">1</span> -->
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>