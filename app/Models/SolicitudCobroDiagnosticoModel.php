<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudCobroDiagnosticoModel extends Model
{
    protected $table            = 'solicitudes_cobro_diagnostico';
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
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getSolicitudesPendientes()
    {
        $db = $this->db;

        return $db->table('solicitudes_cobro_diagnostico scd')
            ->select('scd.*, u.nombres as tecnico_nombre, u.apellidos as tecnico_apellido,
                      d.serie_imei, d.marca_custom, d.modelo_custom,
                      ot.codigo_orden, c.nombres as cliente_nombre')
            ->join('usuarios u', 'u.id = scd.tecnico_id')
            ->join('dispositivos d', 'd.id = scd.dispositivo_id')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->where('scd.estado', 'pendiente')
            ->orderBy('scd.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }
}
