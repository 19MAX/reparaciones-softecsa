<p>Hola <?= esc($datos['cliente_nombre']) ?>,</p>
<p>Hemos recibido tu <strong><?= esc($datos['tipo_dispositivo']) ?>
   <?= esc($datos['marca']) ?> <?= esc($datos['modelo']) ?></strong>
   bajo la orden <strong><?= esc($datos['codigo_orden']) ?></strong>.</p>
<p>Adjuntamos el comprobante de ingreso. Te notificaremos cada avance.</p>
<p><?= esc($empresa_config['nombre_empresa']) ?></p>