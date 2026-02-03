<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionTecnicoModel extends Model
{
    protected $table            = 'comisiones_tecnicos';
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

    public function getComisionesPorTecnico($tecnicoId, $pagado = null)
    {
        $builder = $this->where('tecnico_id', $tecnicoId);

        if ($pagado !== null) {
            $builder->where('pagado', $pagado);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function calcularComision($manoObra, $tipoCalculo, $valorConfig)
    {
        if ($tipoCalculo === 'porcentaje') {
            return ($manoObra * $valorConfig) / 100;
        } else {
            return $valorConfig;
        }
    }

    public function getResumenComisiones($tecnicoId, $mes = null, $anio = null)
    {
        $db = $this->db;

        $builder = $db->table('comisiones_tecnicos')
            ->select('SUM(comision_calculada) as total,
                      SUM(CASE WHEN pagado = 1 THEN comision_calculada ELSE 0 END) as pagado,
                      SUM(CASE WHEN pagado = 0 THEN comision_calculada ELSE 0 END) as pendiente,
                      COUNT(*) as total_trabajos')
            ->where('tecnico_id', $tecnicoId);

        if ($mes && $anio) {
            $builder->where('MONTH(created_at)', $mes)
                    ->where('YEAR(created_at)', $anio);
        }

        return $builder->get()->getRowArray();
    }
}
