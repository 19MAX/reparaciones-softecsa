<?php

namespace App\Models;

use CodeIgniter\Model;

class TecnicoModel extends Model
{
    protected $table            = 'usuarios';
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

    public function getTecnicosActivos()
    {
        return $this->where('role', 'tecnico')
            ->where('estado', 'activo')
            ->orderBy('nombres', 'ASC')
            ->findAll();
    }

    public function getRendimientoTecnico($tecnicoId, $fechaInicio = null, $fechaFin = null)
    {
        $db = $this->db;

        $builder = $db->table('dispositivos d')
            ->select('COUNT(DISTINCT d.id) as total_dispositivos,
                      SUM(CASE WHEN d.estado_reparacion = "reparado" THEN 1 ELSE 0 END) as reparados,
                      SUM(CASE WHEN d.estado_reparacion = "no_reparado" THEN 1 ELSE 0 END) as no_reparados,
                      AVG(d.tiempo_diagnostico_minutos) as tiempo_promedio_diagnostico')
            ->where('d.tecnico_id', $tecnicoId);

        if ($fechaInicio && $fechaFin) {
            $builder->where('d.created_at >=', $fechaInicio)
                    ->where('d.created_at <=', $fechaFin);
        }

        $rendimiento = $builder->get()->getRowArray();

        // Calcular tasa de éxito
        if ($rendimiento['total_dispositivos'] > 0) {
            $rendimiento['tasa_exito'] = round(
                ($rendimiento['reparados'] / $rendimiento['total_dispositivos']) * 100,
                2
            );
        } else {
            $rendimiento['tasa_exito'] = 0;
        }

        // Obtener reclamos de garantía
        $reclamos = $db->table('garantias g')
            ->select('COUNT(rg.id) as total_reclamos,
                      SUM(CASE WHEN rg.problema_cubierto_garantia = 1 THEN 1 ELSE 0 END) as reclamos_validos')
            ->join('reclamos_garantia rg', 'rg.garantia_id = g.id', 'left')
            ->where('g.tecnico_id', $tecnicoId);

        if ($fechaInicio && $fechaFin) {
            $reclamos->where('g.created_at >=', $fechaInicio)
                     ->where('g.created_at <=', $fechaFin);
        }

        $rendimiento['reclamos'] = $reclamos->get()->getRowArray();

        // Obtener comisiones
        $comisiones = $db->table('comisiones_tecnicos')
            ->select('SUM(comision_calculada) as total_comisiones,
                      SUM(CASE WHEN pagado = 1 THEN comision_calculada ELSE 0 END) as comisiones_pagadas')
            ->where('tecnico_id', $tecnicoId);

        if ($fechaInicio && $fechaFin) {
            $comisiones->where('created_at >=', $fechaInicio)
                       ->where('created_at <=', $fechaFin);
        }

        $rendimiento['comisiones'] = $comisiones->get()->getRowArray();

        return $rendimiento;
    }

    public function getEspecialidadTecnico($tecnicoId)
    {
        $db = $this->db;

        return $db->table('dispositivo_problemas dp')
            ->select('pc.nombre as problema, pc.categoria,
                      COUNT(dp.id) as casos_atendidos,
                      SUM(CASE WHEN dp.fue_reparado = 1 THEN 1 ELSE 0 END) as exitosos,
                      ROUND(SUM(CASE WHEN dp.fue_reparado = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(dp.id), 2) as tasa_exito,
                      AVG(dp.tiempo_invertido) as tiempo_promedio')
            ->join('dispositivos d', 'd.id = dp.dispositivo_id')
            ->join('problemas_comunes pc', 'pc.id = dp.problema_comun_id')
            ->where('d.tecnico_id', $tecnicoId)
            ->groupBy('pc.id')
            ->having('casos_atendidos >=', 5)
            ->orderBy('tasa_exito', 'DESC')
            ->get()
            ->getResultArray();
    }
}
