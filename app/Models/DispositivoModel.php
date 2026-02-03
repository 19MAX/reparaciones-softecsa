<?php

namespace App\Models;

use CodeIgniter\Model;

class DispositivoModel extends Model
{
    protected $table = 'dispositivos';
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
    protected $allowCallbacks = false;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getListosParaEntregar()
    {
        $db = $this->db;

        return $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, c.nombres, c.apellidos, c.telefono,
                      td.nombre as tipo_nombre')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->whereIn('d.estado_reparacion', ['reparado', 'no_reparado'])
            ->where('ot.estado_global !=', 'entregada')
            ->orderBy('d.fecha_estimada_entrega', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function verificarTodosEntregados($ordenId)
    {
        $total = $this->where('orden_id', $ordenId)->countAllResults();
        $entregados = $this->where('orden_id', $ordenId)
            ->where('estado_reparacion', 'entregado')
            ->countAllResults();

        return $total === $entregados;
    }

    public function buscarParaGarantia($search)
    {
        $db = $this->db;

        return $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, ot.id as orden_id, 
                      c.nombres, c.apellidos, c.telefono,
                      td.nombre as tipo_nombre')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->groupStart()
            ->like('d.serie_imei', $search)
            ->orLike('ot.codigo_orden', $search)
            ->groupEnd()
            ->where('d.estado_reparacion', 'entregado')
            ->orderBy('d.created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    public function getDispositivosPorTecnico($tecnicoId, $estado = null)
    {
        $db = $this->db;

        $builder = $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, c.nombres, c.apellidos,
                      td.nombre as tipo_nombre, urg.nombre as urgencia_nombre,
                      urg.color_hex as urgencia_color')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->join('urgencias urg', 'urg.id = ot.urgencia_id')
            ->where('d.tecnico_id', $tecnicoId);

        if ($estado) {
            $builder->where('d.estado_reparacion', $estado);
        }

        return $builder->orderBy('d.fecha_estimada_entrega', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDispositivosSinAsignar()
    {
        $db = $this->db;

        return $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, c.nombres, c.apellidos,
                      td.nombre as tipo_nombre, urg.nombre as urgencia_nombre,
                      urg.color_hex as urgencia_color, urg.orden_prioridad')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->join('urgencias urg', 'urg.id = ot.urgencia_id')
            ->where('d.tecnico_id', null)
            ->where('d.estado_reparacion', 'pendiente')
            ->orderBy('urg.orden_prioridad', 'ASC')
            ->orderBy('d.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDispositivoCompleto($dispositivoId)
    {
        $db = $this->db;

        $dispositivo = $db->table('dispositivos d')
            ->select('d.*, ot.codigo_orden, ot.urgencia_id, ot.cliente_id,
                      c.nombres, c.apellidos, c.telefono, c.email,
                      td.nombre as tipo_nombre, td.icono as tipo_icono,
                      m.nombre as marca_nombre, mo.nombre as modelo_nombre,
                      tec.nombres as tecnico_nombre, tec.apellidos as tecnico_apellido,
                      urg.nombre as urgencia_nombre, urg.color_hex as urgencia_color')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = d.tipo_dispositivo_id')
            ->join('marcas m', 'm.id = d.marca_id', 'left')
            ->join('modelos mo', 'mo.id = d.modelo_id', 'left')
            ->join('usuarios tec', 'tec.id = d.tecnico_id', 'left')
            ->join('urgencias urg', 'urg.id = ot.urgencia_id')
            ->where('d.id', $dispositivoId)
            ->get()
            ->getRowArray();

        if (!$dispositivo) {
            return null;
        }

        // Problemas
        $dispositivo['problemas'] = $db->table('dispositivo_problemas dp')
            ->select('dp.*, pc.nombre as problema_nombre, pc.categoria,
                      pc.tiempo_promedio_reparacion, pc.costo_promedio')
            ->join('problemas_comunes pc', 'pc.id = dp.problema_comun_id', 'left')
            ->where('dp.dispositivo_id', $dispositivoId)
            ->get()
            ->getResultArray();

        // Checklist
        $dispositivo['checklist'] = $db->table('checklist_respuestas cr')
            ->select('cr.*, ci.nombre as item_nombre, ci.categoria, ci.es_critico')
            ->join('checklist_items ci', 'ci.id = cr.checklist_item_id')
            ->where('cr.dispositivo_id', $dispositivoId)
            ->get()
            ->getResultArray();

        // Accesorios
        $dispositivo['accesorios'] = $db->table('dispositivo_accesorios da')
            ->select('da.*, a.nombre as accesorio_nombre')
            ->join('accesorios a', 'a.id = da.accesorio_id')
            ->where('da.dispositivo_id', $dispositivoId)
            ->get()
            ->getResultArray();

        // Imágenes
        $dispositivo['imagenes'] = $db->table('dispositivo_imagenes')
            ->where('dispositivo_id', $dispositivoId)
            ->orderBy('tipo', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        // Historial de cambios
        $dispositivo['historial'] = $db->table('historial_cliente hc')
            ->select('hc.*, u.nombres as usuario_nombre')
            ->join('usuarios u', 'u.id = hc.usuario_id')
            ->where('hc.dispositivo_id', $dispositivoId)
            ->orderBy('hc.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Garantía activa
        $dispositivo['garantia'] = $db->table('garantias g')
            ->select('g.*, tg.nombre as tipo_garantia_nombre, tg.dias_garantia,
                      tg.descripcion_cliente, tg.exclusiones')
            ->join('tipos_garantia tg', 'tg.id = g.tipo_garantia_id')
            ->where('g.dispositivo_id', $dispositivoId)
            ->where('g.estado', 'activa')
            ->get()
            ->getRowArray();

        return $dispositivo;
    }

    /**
     * Obtener todos los problemas de un dispositivo con información del problema común
     */
    public function getProblemasByDispositivo($dispositivoId)
    {
        return $this->select('dispositivo_problemas.*, problemas_comunes.nombre as problema_nombre, problemas_comunes.categoria, problemas_comunes.tiempo_estimado')
            ->join('problemas_comunes', 'problemas_comunes.id = dispositivo_problemas.problema_comun_id', 'left')
            ->where('dispositivo_problemas.dispositivo_id', $dispositivoId)
            ->findAll();
    }

    /**
     * Marcar un problema como reparado
     */
    public function marcarComoReparado($problemaId, $costoReparacion = null, $tiempoInvertido = null)
    {
        $data = [
            'fue_reparado' => true,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($costoReparacion !== null) {
            $data['costo_reparacion'] = $costoReparacion;
        }

        if ($tiempoInvertido !== null) {
            $data['tiempo_invertido'] = $tiempoInvertido;
        }

        return $this->update($problemaId, $data);
    }

    /**
     * Obtener problemas pendientes de un dispositivo
     */
    public function getProblemasPendientes($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)
            ->where('fue_reparado', null)
            ->orWhere('fue_reparado', false)
            ->findAll();
    }

    /**
     * Obtener costo total de reparaciones de un dispositivo
     */
    public function getCostoTotalReparaciones($dispositivoId)
    {
        $result = $this->selectSum('costo_reparacion')
            ->where('dispositivo_id', $dispositivoId)
            ->where('fue_reparado', true)
            ->first();

        return $result['costo_reparacion'] ?? 0;
    }

    /**
     * Obtener tiempo total invertido en reparaciones
     */
    public function getTiempoTotalInvertido($dispositivoId)
    {
        $result = $this->selectSum('tiempo_invertido')
            ->where('dispositivo_id', $dispositivoId)
            ->where('fue_reparado', true)
            ->first();

        return $result['tiempo_invertido'] ?? 0;
    }
}
