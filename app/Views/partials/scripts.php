<script src="<?= base_url('assets') ?>/js/core/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets') ?>/js/core/popper.min.js"></script>
<script src="<?= base_url('assets') ?>/js/core/bootstrap.min.js"></script>

<!-- jQuery Scrollbar -->
<script src="<?= base_url('assets') ?>/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

<!-- Chart JS -->
<script src="<?= base_url('assets') ?>/js/plugin/chart.js/chart.min.js"></script>

<!-- jQuery Sparkline -->
<script src="<?= base_url('assets') ?>/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

<!-- Chart Circle -->
<script src="<?= base_url('assets') ?>/js/plugin/chart-circle/circles.min.js"></script>

<!-- Datatables -->
<!-- <script src="<?= base_url('assets') ?>/js/plugin/datatables/datatables.min.js"></script> -->

<script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.6/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.colVis.min.js"></script>


<!-- Bootstrap Notify -->
<script src="<?= base_url('assets') ?>/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<!-- jQuery Vector Maps -->
<script src="<?= base_url('assets') ?>/js/plugin/jsvectormap/jsvectormap.min.js"></script>
<script src="<?= base_url('assets') ?>/js/plugin/jsvectormap/world.js"></script>

<!-- Sweet Alert -->
<script src="<?= base_url('assets') ?>/js/plugin/sweetalert/sweetalert.min.js"></script>

<!-- Kaiadmin JS -->
<script src="<?= base_url('assets') ?>/js/kaiadmin.min.js"></script>

<!-- SWEETALERT2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/sweetalert2.js') ?>"></script>

<script>

   // Verificar si hay mensajes de éxito, advertencia o error
   <?php if (session()->has('flashMessages')): ?>
      <?php foreach (session('flashMessages') as $message): ?>
         <?php
         $type = $message[1];
         $msg = $message[0];
         $position = $message[2] ?? 'top-end';
         $codigo = $message[3] ?? null;
         $urlConsulta = $message[4] ?? null;
         ?>
         showAlert(
            <?= json_encode($type ?? "") ?>,
            <?= json_encode($msg ?? "") ?>,
            <?= json_encode($position ?? "") ?>,
            <?= json_encode($codigo ?? "") ?>,
            <?= json_encode($urlConsulta ?? "") ?>,
            <?= json_encode($cedula ?? "") ?>
         );
      <?php endforeach; ?>
   <?php endif; ?>
   const base_url = '<?= base_url() ?>';
</script>