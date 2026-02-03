<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdenFinalizadaModel extends Model
{
    protected $table            = 'ordenes_finalizadas';
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

    public function getIngresosDelMes($mes = null, $anio = null)
    {
        $mes = $mes ?? date('m');
        $anio = $anio ?? date('Y');

        return $this->select('SUM(total_facturado) as total_facturado,
                              SUM(abonos_recibidos) as total_cobrado,
                              SUM(saldo_pendiente) as total_pendiente,
                              COUNT(*) as total_ordenes')
            ->where('MONTH(fecha_finalizacion)', $mes)
            ->where('YEAR(fecha_finalizacion)', $anio)
            ->first();
    }

}
