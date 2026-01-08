<!DOCTYPE html>
<html lang="es">

<head>
    <?= $this->include('partials/head') ?>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?= $this->include('partials/sidebar') ?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <div class="logo-header" data-background-color="dark">

                        <a href="index.html" class="logo">
                            <img src="<?= base_url('assets') ?>/img/kaiadmin/logo_light.svg" alt="navbar brand"
                                class="navbar-brand" height="20">
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
                <!-- Navbar Header -->
                <?= $this->include('partials/navbar') ?>
                <!-- End Navbar -->
            </div>

            <div class="container">
                <div class="page-inner">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <?= $this->include('partials/footer') ?>
        </div>

        <!-- Custom template | don't include it in your project! -->
        <?= $this->include('partials/custom-template') ?>
        <!-- End Custom template -->
    </div>
    <!--   Core JS Files   -->
    <?= $this->include('partials/scripts') ?>
    <?= $this->renderSection('scripts') ?>
</body>

</html>