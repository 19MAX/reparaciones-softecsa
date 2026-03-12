<?php

namespace App\Models;

use CodeIgniter\Model;

class DispositivosOrdenModel extends Model
{
    protected $table = 'dispositivos_orden';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getDetalleCompleto(int $dispositivoId): ?array
    {
        $db = \Config\Database::connect();

        // ── 1. Dispositivo + info relacionada ─────────────────────────
        $dispositivo = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.serie_imei',
                'do.tipo_seguridad',
                'do.relato_cliente',
                'do.estado',
                'do.costo_prioridad',
                'do.precio_total',
                'do.tiempo_total_horas',
                'do.comision_tecnico',
                'do.fecha_estimada_entrega',
                'do.fecha_real_entrega',
                'do.created_at              AS fecha_ingreso',
                'do.tipo_seguridad AS tipo_pass',
                'do.clave_acceso',
                // Tipo, marca, modelo
                'td.nombre                  AS tipo_dispositivo',
                'm.nombre                   AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                // Prioridad
                'pr.nombre                  AS prioridad',
                'pr.color_badge             AS prioridad_color',
                // Orden
                'o.id                       AS orden_id',
                'o.numero_orden AS codigo_orden',
                // Cliente
                'c.id                       AS cliente_id',
                'c.nombres                   AS cliente_nombre',
                'c.telefono                 AS cliente_telefono',
                'c.email                    AS cliente_email',
                // Técnico asignado
                'u.id                       AS tecnico_id',
                'u.nombre                   AS tecnico_nombre',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('prioridades pr', 'pr.id = do.prioridad_id', 'left')
            ->join('ordenes o', 'o.id  = do.orden_id')
            ->join('clientes c', 'c.id  = o.cliente_id')
            ->join('usuarios u', 'u.id  = do.tecnico_id', 'left')
            ->where('do.id', $dispositivoId)
            ->get()
            ->getRowArray();

        if (!$dispositivo) {
            return null;
        }

        // ── 2. Problemas del dispositivo con precios ──────────────────
        $dispositivo['problemas'] = $db->table('dispositivo_problemas dp')
            ->select([
                'dp.id',
                'dp.precio_mano_obra',
                'dp.precio_repuesto',
                'dp.observacion',
                'dp.tiempo_reparacion_horas AS tiempo_estimado_horas',
                'p.nombre                   AS problema',
                // Subtotal por problema
                '(dp.precio_mano_obra + dp.precio_repuesto) AS subtotal',
            ])
            ->join('problemas p', 'p.id = dp.problema_id')
            ->where('dp.dispositivo_orden_id', $dispositivoId)
            ->orderBy('dp.id', 'ASC')
            ->get()
            ->getResultArray();

        // ── 3. Accesorios ─────────────────────────────────────────────
        $dispositivo['accesorios'] = $db->table('dispositivo_accesorios da')
            ->select([
                'da.id',
                'da.cantidad',
                'da.observacion',
                // Nombre del catálogo o texto libre
                'COALESCE(ac.nombre, da.accesorio_texto) AS accesorio',
            ])
            ->join('accesorios_catalogo ac', 'ac.id = da.accesorio_id', 'left')
            ->where('da.dispositivo_orden_id', $dispositivoId)
            ->get()
            ->getResultArray();

        // ── 4. Detalles físicos ───────────────────────────────────────
        $dispositivo['detalles'] = $db->table('dispositivo_detalles dd')
            ->select([
                'dd.id',
                'COALESCE(dc.nombre, dd.detalle_texto) AS detalle',
            ])
            ->join('detalles_catalogo dc', 'dc.id = dd.detalle_id', 'left')
            ->where('dd.dispositivo_orden_id', $dispositivoId)
            ->get()
            ->getResultArray();

        // ── 5. Historial de estados ───────────────────────────────────
        $dispositivo['historial'] = $db->table('historial_estados he')
            ->select([
                'he.id',
                'he.estado_anterior',
                'he.estado_nuevo',
                'he.observacion',
                'he.created_at AS fecha',
                'u.nombre      AS usuario',
                'u.rol         AS usuario_rol',
            ])
            ->join('usuarios u', 'u.id = he.usuario_id')
            ->where('he.dispositivo_orden_id', $dispositivoId)
            ->orderBy('he.created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $dispositivo;
    }
}
