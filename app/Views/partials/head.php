<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title> <?= $this->renderSection('title') ?></title>
<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
<link rel="icon" href="<?= base_url('assets') ?>/img/kaiadmin/favicon.ico" type="image/x-icon" />

<!-- Fonts and icons -->
<script src="<?= base_url('assets') ?>/js/plugin/webfont/webfont.min.js"></script>
<script>
    WebFont.load({
        google: { "families": ["Public Sans:300,400,500,600,700"] },
        custom: { "families": ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['<?= base_url('assets') ?>/css/fonts.min.css'] },
        active: function () {
            sessionStorage.fonts = true;
        }
    });
</script>

<!-- CSS Files -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/css/plugins.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/css/kaiadmin.min.css">

<!-- CSS Just for demo purpose, don't include it in your project -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/css/demo.css">

<!-- SWEETALERT2 -->
<link rel="stylesheet" href="<?= base_url('assets/css/sweetalert2.css') ?>">

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">