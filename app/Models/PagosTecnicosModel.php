<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosTecnicosModel extends Model
{
    protected $table            = 'pagos_tecnicos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dispositivo_orden_id',
        'tecnico_id',
        'monto_comision',
        'tipo_comision',
        'porcentaje_aplicado',
        'mano_obra_base',
        'estado_pago',
        'fecha_reparacion',
        'fecha_validacion',
        'fecha_pago',
        'validado_por',
        'observacion',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    // ──────────────────────────────────────────────────────────────────────────
    // Historial completo de pagos de un técnico
    // ──────────────────────────────────────────────────────────────────────────
    public function getHistorialTecnico(int $tecnicoId): array
    {
        $db = \Config\Database::connect();

        return $db->table('pagos_tecnicos pt')
            ->select([
                'pt.id',
                'pt.monto_comision',
                'pt.tipo_comision',
                'pt.porcentaje_aplicado',
                'pt.mano_obra_base',
                'pt.estado_pago',
                'pt.fecha_reparacion',
                'pt.fecha_validacion',
                'pt.fecha_pago',
                'pt.observacion',
                // Dispositivo
                'do2.id              AS dispositivo_id',
                'do2.precio_total',
                'td.nombre           AS tipo_dispositivo',
                'm.nombre            AS marca',
                'COALESCE(mo.nombre, do2.modelo_texto) AS modelo',
                // Orden
                'o.numero_orden',
                // Cliente
                'c.nombres           AS cliente_nombre',
                'c.apellidos         AS cliente_apellido',
                // Admin que validó/pagó
                'uv.nombre           AS validado_por_nombre',
            ])
            ->join('dispositivos_orden do2', 'do2.id = pt.dispositivo_orden_id')
            ->join('tipos_dispositivo td',  'td.id  = do2.tipo_dispositivo_id')
            ->join('marcas m',              'm.id   = do2.marca_id')
            ->join('modelos mo',            'mo.id  = do2.modelo_id',    'left')
            ->join('ordenes o',             'o.id   = do2.orden_id')
            ->join('clientes c',            'c.id   = o.cliente_id')
            ->join('usuarios uv',           'uv.id  = pt.validado_por',  'left')
            ->where('pt.tecnico_id', $tecnicoId)
            ->orderBy('pt.fecha_reparacion', 'DESC')
            ->get()
            ->getResultArray();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Listado de todos los pagos (para panel admin) con info de técnico
    // ──────────────────────────────────────────────────────────────────────────
    public function getListadoAdmin(?string $estadoPago = null, ?int $tecnicoId = null): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('pagos_tecnicos pt')
            ->select([
                'pt.id',
                'pt.monto_comision',
                'pt.tipo_comision',
                'pt.porcentaje_aplicado',
                'pt.mano_obra_base',
                'pt.estado_pago',
                'pt.fecha_reparacion',
                'pt.fecha_validacion',
                'pt.fecha_pago',
                'pt.observacion',
                // Técnico
                'u.id                AS tecnico_id',
                'u.nombre            AS tecnico_nombre',
                'u.apellido          AS tecnico_apellido',
                // Dispositivo
                'do2.id              AS dispositivo_id',
                'do2.precio_total',
                'td.nombre           AS tipo_dispositivo',
                'm.nombre            AS marca',
                'COALESCE(mo.nombre, do2.modelo_texto) AS modelo',
                // Orden
                'o.numero_orden',
                // Cliente
                'c.nombres           AS cliente_nombre',
                'c.apellidos         AS cliente_apellido',
                // Admin validador
                'uv.nombre           AS validado_por_nombre',
            ])
            ->join('usuarios u',            'u.id   = pt.tecnico_id')
            ->join('dispositivos_orden do2', 'do2.id = pt.dispositivo_orden_id')
            ->join('tipos_dispositivo td',  'td.id  = do2.tipo_dispositivo_id')
            ->join('marcas m',              'm.id   = do2.marca_id')
            ->join('modelos mo',            'mo.id  = do2.modelo_id',    'left')
            ->join('ordenes o',             'o.id   = do2.orden_id')
            ->join('clientes c',            'c.id   = o.cliente_id')
            ->join('usuarios uv',           'uv.id  = pt.validado_por',  'left')
            ->orderBy('pt.fecha_reparacion', 'DESC');

        if ($estadoPago !== null) {
            $builder->where('pt.estado_pago', $estadoPago);
        }

        if ($tecnicoId !== null) {
            $builder->where('pt.tecnico_id', $tecnicoId);
        }

        return $builder->get()->getResultArray();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Resumen de totales por técnico (para dashboard admin)
    // ──────────────────────────────────────────────────────────────────────────
    public function getResumenPorTecnico(): array
    {
        $db = \Config\Database::connect();

        return $db->query("
            SELECT
                u.id                                          AS tecnico_id,
                CONCAT(u.nombre, ' ', COALESCE(u.apellido,'')) AS tecnico_nombre,
                COUNT(pt.id)                                  AS total_reparaciones,
                SUM(pt.monto_comision)                        AS total_comision,
                SUM(CASE WHEN pt.estado_pago = 'pendiente' THEN pt.monto_comision ELSE 0 END) AS pendiente,
                SUM(CASE WHEN pt.estado_pago = 'validado'  THEN pt.monto_comision ELSE 0 END) AS validado,
                SUM(CASE WHEN pt.estado_pago = 'pagado'    THEN pt.monto_comision ELSE 0 END) AS pagado
            FROM pagos_tecnicos pt
            JOIN usuarios u ON u.id = pt.tecnico_id
            GROUP BY u.id, u.nombre, u.apellido
            ORDER BY u.nombre ASC
        ")->getResultArray();
    }
}
