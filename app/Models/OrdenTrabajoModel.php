<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdenTrabajoModel extends Model
{
    protected $table            = 'ordenes_trabajo';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = false;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function generarCodigo()
    {
        $year = date('Y');
        $ultimaOrden = $this->select('codigo_orden')
            ->like('codigo_orden', 'ORD-' . $year, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($ultimaOrden) {
            $numero = (int) substr($ultimaOrden['codigo_orden'], -6) + 1;
        } else {
            $numero = 1;
        }

        return 'ORD-' . $year . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function getOrdenCompleta($ordenId)
    {
        $db = $this->db;

        $orden = $db->table('ordenes_trabajo ot')
            ->select('ot.*, c.nombres, c.apellidos, c.cedula, c.telefono, c.email, 
                      u.nombres as usuario_nombre, urg.nombre as urgencia_nombre, 
                      urg.recargo as urgencia_recargo, urg.tiempo_espera as urgencia_dias')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('usuarios u', 'u.id = ot.usuario_id')
            ->join('urgencias urg', 'urg.id = ot.urgencia_id')
            ->where('ot.id', $ordenId)
            ->get()
            ->getRowArray();

        if (!$orden) {
            return null;
        }

        // Obtener dispositivos de la orden
        $orden['dispositivos'] = $db->table('dispositivos d')
            ->select('d.*, td.nombre as tipo_nombre, m.nombre as marca_nombre,
                      mo.nombre as modelo_nombre, tec.nombres as tecnico_nombre,
                      tec.apellidos as tecnico_apellido')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->join('marcas m', 'm.id = d.marca_id', 'left')
            ->join('modelos mo', 'mo.id = d.modelo_id', 'left')
            ->join('usuarios tec', 'tec.id = d.tecnico_id', 'left')
            ->where('d.orden_id', $ordenId)
            ->get()
            ->getResultArray();

        // Para cada dispositivo, obtener problemas, checklist, accesorios
        foreach ($orden['dispositivos'] as &$dispositivo) {
            // Problemas
            $dispositivo['problemas'] = $db->table('dispositivo_problemas dp')
                ->select('dp.*, pc.nombre as problema_nombre')
                ->join('problemas_comunes pc', 'pc.id = dp.problema_comun_id', 'left')
                ->where('dp.dispositivo_id', $dispositivo['id'])
                ->get()
                ->getResultArray();

            // Checklist
            $dispositivo['checklist'] = $db->table('checklist_respuestas cr')
                ->select('cr.*, ci.nombre as item_nombre, ci.categoria')
                ->join('checklist_items ci', 'ci.id = cr.checklist_item_id')
                ->where('cr.dispositivo_id', $dispositivo['id'])
                ->get()
                ->getResultArray();

            // Accesorios
            $dispositivo['accesorios'] = $db->table('dispositivo_accesorios da')
                ->select('da.*, a.nombre as accesorio_nombre')
                ->join('accesorios a', 'a.id = da.accesorio_id')
                ->where('da.dispositivo_id', $dispositivo['id'])
                ->get()
                ->getResultArray();

            // Imágenes
            $dispositivo['imagenes'] = $db->table('dispositivo_imagenes')
                ->where('dispositivo_id', $dispositivo['id'])
                ->get()
                ->getResultArray();

            // Garantía activa
            $dispositivo['garantia'] = $db->table('garantias g')
                ->select('g.*, tg.nombre as tipo_garantia_nombre, tg.dias_garantia')
                ->join('tipos_garantia tg', 'tg.id = g.tipo_garantia_id')
                ->where('g.dispositivo_id', $dispositivo['id'])
                ->where('g.estado', 'activa')
                ->get()
                ->getRowArray();
        }

        return $orden;
    }

    public function getOrdenesHoy()
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();
    }

    public function getPendientesEntrega()
    {
        $db = $this->db;

        return $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, c.nombres, c.apellidos, c.telefono')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->whereIn('d.estado_reparacion', ['reparado', 'no_reparado'])
            ->where('ot.estado_global !=', 'entregada')
            ->get()
            ->getResultArray();
    }

    public function buscarOrden($search)
    {
        $db = $this->db;

        return $db->table('ordenes_trabajo ot')
            ->select('ot.*, c.nombres, c.apellidos, c.cedula, c.telefono')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->groupStart()
                ->like('ot.codigo_orden', $search)
                ->orLike('c.cedula', $search)
                ->orLike('c.nombres', $search)
                ->orLike('c.apellidos', $search)
            ->groupEnd()
            ->orderBy('ot.created_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getOrdenesProblematicas()
    {
        $db = $this->db;

        // Órdenes con más de 10 días sin cambio de estado
        return $db->table('ordenes_trabajo ot')
            ->select('ot.*, c.nombres, c.apellidos, 
                      DATEDIFF(NOW(), ot.updated_at) as dias_sin_actualizacion,
                      COUNT(d.id) as total_dispositivos')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('dispositivos d', 'd.orden_id = ot.id', 'left')
            ->whereNotIn('ot.estado_global', ['entregada', 'cancelada'])
            ->having('dias_sin_actualizacion >', 10)
            ->groupBy('ot.id')
            ->orderBy('dias_sin_actualizacion', 'DESC')
            ->get()
            ->getResultArray();
    }
}
