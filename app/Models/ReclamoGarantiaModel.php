<?php

namespace App\Models;

use CodeIgniter\Model;

class ReclamoGarantiaModel extends Model
{
    protected $table            = 'reclamos_garantias';
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

     public function getPendientes()
    {
        $db = $this->db;

        return $db->table('reclamos_garantia rg')
            ->select('rg.*, d.serie_imei, d.marca_custom, d.modelo_custom,
                      c.nombres as cliente_nombre, c.apellidos as cliente_apellido,
                      u.nombres as tecnico_nombre')
            ->join('dispositivos d', 'd.id = rg.dispositivo_id')
            ->join('garantias g', 'g.id = rg.garantia_id')
            ->join('ordenes_trabajo ot', 'ot.id = g.orden_original_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('usuarios u', 'u.id = g.tecnico_id')
            ->where('rg.estado_reclamo', 'pendiente_evaluacion')
            ->orderBy('rg.fecha_reclamo', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getReclamosPorTecnico($tecnicoId, $estado = null)
    {
        $db = $this->db;

        $builder = $db->table('reclamos_garantia rg')
            ->select('rg.*, d.serie_imei, d.marca_custom, d.modelo_custom,
                      c.nombres as cliente_nombre, c.apellidos as cliente_apellido,
                      ot.codigo_orden')
            ->join('dispositivos d', 'd.id = rg.dispositivo_id')
            ->join('garantias g', 'g.id = rg.garantia_id')
            ->join('ordenes_trabajo ot', 'ot.id = g.orden_original_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->where('g.tecnico_id', $tecnicoId);

        if ($estado) {
            $builder->where('rg.estado_reclamo', $estado);
        }

        return $builder->orderBy('rg.fecha_reclamo', 'DESC')
            ->get()
            ->getResultArray();
    }
}
