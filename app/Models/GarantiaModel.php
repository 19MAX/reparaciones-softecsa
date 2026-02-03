<?php

namespace App\Models;

use CodeIgniter\Model;

class GarantiaModel extends Model
{
    protected $table            = 'garantias';
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

    public function getGarantiaActiva($dispositivoId)
    {
        $db = $this->db;

        return $db->table('garantias g')
            ->select('g.*, tg.nombre as tipo_garantia_nombre, tg.dias_garantia,
                      tg.descripcion_cliente, tg.exclusiones,
                      u.nombres as tecnico_nombre, u.apellidos as tecnico_apellido,
                      DATEDIFF(g.fecha_vencimiento, CURDATE()) as dias_restantes')
            ->join('tipos_garantia tg', 'tg.id = g.tipo_garantia_id')
            ->join('usuarios u', 'u.id = g.tecnico_id')
            ->where('g.dispositivo_id', $dispositivoId)
            ->where('g.estado', 'activa')
            ->orderBy('g.created_at', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function getGarantiasPorVencer($dias = 7)
    {
        $db = $this->db;

        return $db->table('garantias g')
            ->select('g.*, d.serie_imei, d.marca_custom, d.modelo_custom,
                      c.nombres as cliente_nombre, c.apellidos as cliente_apellido, c.telefono,
                      u.nombres as tecnico_nombre,
                      DATEDIFF(g.fecha_vencimiento, CURDATE()) as dias_restantes')
            ->join('dispositivos d', 'd.id = g.dispositivo_id')
            ->join('ordenes_trabajo ot', 'ot.id = g.orden_original_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('usuarios u', 'u.id = g.tecnico_id')
            ->where('g.estado', 'activa')
            ->where('g.fecha_vencimiento <=', date('Y-m-d', strtotime("+$dias days")))
            ->where('g.fecha_vencimiento >=', date('Y-m-d'))
            ->orderBy('g.fecha_vencimiento', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function marcarComoVencidas()
    {
        return $this->where('estado', 'activa')
            ->where('fecha_vencimiento <', date('Y-m-d'))
            ->set(['estado' => 'vencida'])
            ->update();
    }
}
