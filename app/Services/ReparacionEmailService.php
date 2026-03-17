<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class ReparacionEmailService
{
    private \CodeIgniter\Email\Email $mailer;
    private array $empresaConfig;

    public function __construct()
    {
        $this->mailer = service('email', null, false);
        $this->empresaConfig = model('ConfiguracionModel')->getConfig();
    }

    // ══════════════════════════════════════════════════════════════════
    //  MÉTODOS PÚBLICOS — uno por evento de negocio
    // ══════════════════════════════════════════════════════════════════

    /**
     * Al INICIAR reparación → envía el PDF de la orden como adjunto.
     */
    public function enviarIngresoOrden(int $ordenId, int $dispositivoId, ?string $tipoImpresion = 'ticket'): bool
    {
        $datos = $this->getDatosDispositivo($dispositivoId);
        if (!$datos || empty($datos['cliente_email'])) {
            return false;
        }

        $pdfBytes = $this->generarPdfOrden($ordenId, $tipoImpresion);

        $asunto = "📋 Orden {$datos['codigo_orden']} recibida — {$this->empresaConfig['nombre_empresa']}";

        $cuerpo = $this->renderVista('emails/ingreso_orden', [
            'datos' => $datos,
            'empresa_config' => $this->empresaConfig,
        ]);

        return $this->enviar(
            to: $datos['cliente_email'],
            nombre: $datos['cliente_nombre'],
            asunto: $asunto,
            cuerpo: $cuerpo,
            adjuntoPdf: $pdfBytes,
            nombrePdf: "Orden_{$datos['codigo_orden']}.pdf",
        );
    }

    /**
     * Al CAMBIAR estado (en_proceso, pausado, listo, etc.) → solo texto.
     */
    public function enviarCambioEstado(
        int $dispositivoId,
        string $estadoNuevo,
        string $comentarioCliente = ''
    ): bool {
        $datos = $this->getDatosDispositivo($dispositivoId);
        if (!$datos || empty($datos['cliente_email'])) {
            return false;
        }

        $etiquetas = [
            'en_proceso' => '🔧 En reparación',
            'pausado' => '⏸ En pausa',
            'listo' => '✅ Listo para retirar',
            'cancelado' => '❌ Cancelado / No reparable',
            'entregado' => '📦 Entregado',
        ];

        $etiqueta = $etiquetas[$estadoNuevo] ?? ucfirst($estadoNuevo);
        $asunto = "{$etiqueta} — Orden {$datos['codigo_orden']}";

        $cuerpo = $this->renderVista('emails/cambio_estado', [
            'datos' => $datos,
            'estado_nuevo' => $estadoNuevo,
            'etiqueta_estado' => $etiqueta,
            'comentario_cliente' => $comentarioCliente,
            'empresa_config' => $this->empresaConfig,
        ]);

        return $this->enviar(
            to: $datos['cliente_email'],
            nombre: $datos['cliente_nombre'],
            asunto: $asunto,
            cuerpo: $cuerpo,
        );
    }

    public function iniciarReparacion(int $dispositivoId, string $estadoFinal, string $comentario): bool
    {
        return $this->enviarCambioEstado($dispositivoId, $estadoFinal, $comentario);
    }
    /**
     * Al FINALIZAR reparación (listo / cancelado) → texto + sin PDF.
     * Si el estado es "listo" agrega el precio total.
     */
    public function enviarFinalizacion(int $dispositivoId, string $estadoFinal, string $comentario): bool
    {
        return $this->enviarCambioEstado($dispositivoId, $estadoFinal, $comentario);
    }

    /**
     * Al ENTREGAR el dispositivo → texto confirmando la entrega.
     */
    public function enviarEntrega(int $dispositivoId): bool
    {
        return $this->enviarCambioEstado($dispositivoId, 'entregado', '');
    }

    // ══════════════════════════════════════════════════════════════════
    //  MÉTODOS PRIVADOS — infraestructura interna
    // ══════════════════════════════════════════════════════════════════

    /**
     * Envío base. El PDF es opcional: solo se adjunta si se pasa $adjuntoPdf.
     */
    private function enviar(
        string $to,
        string $nombre,
        string $asunto,
        string $cuerpo,
        ?string $adjuntoPdf = null,
        string $nombrePdf = 'documento.pdf',
    ): bool {
        try {
            $this->mailer->clear(true);

            $this->mailer->setFrom(
                $this->empresaConfig['email_remitente'] ?? config('Email')->fromEmail,
                $this->empresaConfig['nombre_empresa'] ?? config('Email')->fromName,
            );

            $this->mailer->setTo($to);
            $this->mailer->setSubject($asunto);
            $this->mailer->setMessage($cuerpo);
            $this->mailer->setMailType('html');

            if ($adjuntoPdf !== null) {
                // Igual que el job: guardar temporal → adjuntar ruta → eliminar
                $tempPath = WRITEPATH . 'uploads/' . $nombrePdf;
                file_put_contents($tempPath, $adjuntoPdf);

                $this->mailer->attach($tempPath); // solo la ruta, sin parámetros nombrados

                $result = $this->mailer->send(false);

                // Limpiar temporal siempre, haya enviado o no
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            } else {
                $result = $this->mailer->send(false);
            }

            if (!$result) {
                log_message('error', '[ReparacionEmailService] ' . $this->mailer->printDebugger(['headers']));
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            log_message('error', '[ReparacionEmailService] ' . $e->getMessage());
            // Limpiar temporal si el error ocurrió después de crearlo
            if (!empty($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }
            return false;
        }
    }

    /**
     * Genera el PDF de una orden en memoria (igual que imprimir() pero
     * devuelve los bytes en lugar de hacer stream al navegador).
     */
    private function generarPdfOrden(int $ordenId, ?string $tipoImpresion = 'ticket'): string
    {
        $db = \Config\Database::connect();

        $orden = $db->table('ordenes o')
            ->select([
                'o.id',
                'o.numero_orden',
                'o.estado',
                'o.observaciones_generales',
                'o.created_at AS fecha_ingreso',
                'c.nombres AS cliente_nombre',
                'c.telefono AS cliente_telefono',
                'c.email AS cliente_email',
                'c.cedula AS cliente_cedula',
                'u.nombre AS recepcionista',
            ])
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('usuarios u', 'u.id = o.usuario_recepcion_id')
            ->where('o.id', $ordenId)
            ->get()->getRowArray();

        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.serie_imei',
                'do.tipo_seguridad',
                'do.relato_cliente',
                'do.precio_total',
                'do.costo_prioridad',
                'do.tiempo_total_horas',
                'do.fecha_estimada_entrega',
                'do.fecha_real_entrega',
                'do.created_at AS fecha_ingreso',
                'td.nombre AS tipo_dispositivo',
                'm.nombre AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                'pr.nombre AS prioridad',
                'pr.color_badge AS prioridad_color',
                'u.nombre AS tecnico',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('prioridades pr', 'pr.id = do.prioridad_id', 'left')
            ->join('usuarios u', 'u.id  = do.tecnico_id', 'left')
            ->where('do.orden_id', $ordenId)
            ->orderBy('do.id', 'ASC')
            ->get()->getResultArray();

        foreach ($dispositivos as &$dev) {
            $devId = $dev['id'];

            $dev['problemas'] = $db->table('dispositivo_problemas dp')
                ->select([
                    'p.nombre AS problema',
                    'dp.precio_mano_obra',
                    'dp.precio_repuesto',
                    '(dp.precio_mano_obra + dp.precio_repuesto) AS subtotal',
                    'dp.observacion',
                ])
                ->join('problemas p', 'p.id = dp.problema_id')
                ->where('dp.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            $dev['accesorios'] = $db->table('dispositivo_accesorios da')
                ->select('COALESCE(ac.nombre, da.accesorio_texto) AS accesorio, da.cantidad')
                ->join('accesorios_catalogo ac', 'ac.id = da.accesorio_id', 'left')
                ->where('da.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            $dev['detalles'] = $db->table('dispositivo_detalles dd')
                ->select('COALESCE(dc.nombre, dd.detalle_texto) AS detalle')
                ->join('detalles_catalogo dc', 'dc.id = dd.detalle_id', 'left')
                ->where('dd.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            $ultimaObs = $db->table('historial_estados')
                ->select('observacion_cliente')
                ->where('dispositivo_orden_id', $devId)
                ->where('observacion_cliente IS NOT NULL', null, false)
                ->orderBy('id', 'DESC')->limit(1)
                ->get()->getRowArray();

            $dev['comentario_cliente'] = $ultimaObs['observacion_cliente'] ?? null;
        }
        unset($dev);

        // Términos
        $terminos = [];
        if (!empty($this->empresaConfig['terminos_condiciones'])) {
            $decoded = json_decode($this->empresaConfig['terminos_condiciones'], true);
            $terminos = is_array($decoded) ? $decoded : [];
        }
        if (empty($terminos)) {
            $terminos = [
                'El taller no se hace responsable por daños preexistentes no reportados al momento del ingreso del equipo.',
                'El cliente debe retirar su equipo dentro de los 30 días posteriores a la notificación de reparación completada.',
                'Los equipos no retirados en el plazo indicado podrán generar costos de almacenamiento.',
                'La garantía de reparación cubre únicamente la falla reparada y tiene una duración de 30 días.',
                'El retiro del equipo implica la aceptación del trabajo realizado y el monto cobrado.',
            ];
        }

        $urlSeguimiento = base_url("consulta/orden/" . $orden['numero_orden']);
        $qrCodeBase64 = (new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $urlSeguimiento,
            encoding: new Encoding('UTF-8'),
            size: 100,
            margin: 0
        ))->build()->getDataUri();

        // Agrupamos los datos en un solo array para que sea más limpio
        $dataVista = [
            'orden' => $orden,
            'dispositivos' => $dispositivos,
            'qr_code' => $qrCodeBase64,           // sin QR en el email para simplificar
            'empresa_config' => $this->empresaConfig,
            'terminos' => $terminos,
        ];

        // ── Inicializar Dompdf ──
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);

        // ── Validaciones de formato ──
        $vista = 'admin/ordenes/pdf_orden';
        $tamanioPapel = 'A4';
        $orientacion = 'landscape';

        if ($tipoImpresion === 'ticket') {
            $vista = 'admin/pdf/orden_ticket';
            $tamanioPapel = [0, 0, 226.77, 800];
            $orientacion = 'portrait';
        } elseif ($tipoImpresion === 'carta') {
            $vista = 'admin/pdf/orden_carta';
            $tamanioPapel = 'carta';
            $orientacion = 'portrait';
        }

        // ── Renderizado ──
        $html = view($vista, $dataVista);

        $dompdf->loadHtml($html);
        $dompdf->setPaper($tamanioPapel, $orientacion);
        $dompdf->render();

        return $dompdf->output(); // bytes en memoria, sin stream
    }

    /**
     * Trae los datos mínimos del dispositivo necesarios para los emails.
     */
    private function getDatosDispositivo(int $dispositivoId): ?array
    {
        $db = \Config\Database::connect();

        return $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.precio_total',
                'do.fecha_estimada_entrega',
                'td.nombre                           AS tipo_dispositivo',
                'm.nombre                            AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                'o.numero_orden                      AS codigo_orden',
                'o.id                                AS orden_id',
                'c.nombres                           AS cliente_nombre',
                'c.email                             AS cliente_email',
            ])
            ->join('ordenes o', 'o.id  = do.orden_id')
            ->join('clientes c', 'c.id  = o.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->where('do.id', $dispositivoId)
            ->get()->getRowArray() ?: null;
    }

    /**
     * Renderiza una vista de CI4 a string HTML para usarla como cuerpo del email.
     */
    private function renderVista(string $vista, array $datos = []): string
    {
        return view($vista, $datos);
    }
}