<?php

namespace App\Models;

use CodeIgniter\Model;

class ExcepcionGarantiaModel extends Model
{
    protected $table            = 'excepciones_garantia';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
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
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getExcepcionesPendientes()
    {
        $db = $this->db;

        return $db->table('excepciones_garantia eg')
            ->select('eg.*, d.serie_imei, d.marca_custom, d.modelo_custom,
                      c.nombres as cliente_nombre, c.apellidos as cliente_apellido,
                      u.nombres as solicitante_nombre,
                      ot.codigo_orden,
                      (SELECT COUNT(*) FROM ordenes_trabajo WHERE cliente_id = c.id) as ordenes_cliente')
            ->join('dispositivos d', 'd.id = eg.dispositivo_id')
            ->join('garantias g', 'g.id = eg.garantia_vencida_id')
            ->join('ordenes_trabajo ot', 'ot.id = g.orden_original_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('usuarios u', 'u.id = eg.solicitante_id')
            ->where('eg.estado', 'pendiente')
            ->orderBy('eg.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

}
