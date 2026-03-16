<p>Hola <?= esc($datos['cliente_nombre']) ?>,</p>
<p>Tu dispositivo <strong><?= esc($datos['marca']) ?> <?= esc($datos['modelo']) ?></strong>
   (Orden <?= esc($datos['codigo_orden']) ?>) ahora está en estado:
   <strong><?= esc($etiqueta_estado) ?></strong>.</p>

<?php if (!empty($comentario_cliente)): ?>
    <p><em><?= esc($comentario_cliente) ?></em></p>
<?php endif; ?>

<?php if ($estado_nuevo === 'listo'): ?>
    <p>💰 Total a pagar: <strong>$<?= number_format($datos['precio_total'], 2) ?></strong></p>
    <p>Puedes pasar a retirar tu equipo cuando gustes.</p>
<?php endif; ?>

<p><?= esc($empresa_config['nombre_empresa']) ?></p>