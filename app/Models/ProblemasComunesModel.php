<?php

namespace App\Models;

use CodeIgniter\Model;

class ProblemasComunesModel extends Model
{
    protected $table = 'problemas_comunes';
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

    public function buscarProblemas($query, $tipoDispositivoId = null)
    {
        $builder = $this->like('nombre', $query)
            ->where('activo', 1);

        if ($tipoDispositivoId) {
            $builder->groupStart()
                ->where('tipo_dispositivo_id', $tipoDispositivoId)
                ->orWhere('tipo_dispositivo_id', null)
                ->groupEnd();
        }

        return $builder->orderBy('veces_reportado', 'DESC')
            ->limit(10)
            ->findAll();
    }

    public function getProblemasMasComunes($limite = 10)
    {
        return $this->where('activo', 1)
            ->orderBy('veces_reportado', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    public function getEstadisticasProblemas()
    {
        $db = $this->db;

        return $db->table('problemas_comunes pc')
            ->select('pc.nombre, pc.categoria, pc.veces_reportado,
            COUNT(DISTINCT dp.id) as casos_totales,
            SUM(CASE WHEN dp.fue_reparado =1 THEN 1 ELSE 0 END) as casos_exitosos,
            SUM(CASE WHEN dp.fue_reparado = 0 THEN 1 ELSE 0 END) as casos_fallidos,
            ROUND(SUM(CASE WHEN dp.fue_reparado = 1 THEN 1 ELSE 0 END) * 100.0 /
            NULLIF(COUNT(DISTINCT dp.id), 0), 2) as tasa_exito,
            AVG(dp.tiempo_invertido) as tiempo_promedio,
            AVG(dp.costo_reparacion) as costo_promedio,
            SUM(dp.costo_reparacion) as ganancia_total')
            ->join('dispositivo_problemas dp', 'dp.problema_comun_id = pc.id', 'left')
            ->where('pc.activo', 1)
            ->groupBy('pc.id')
            ->orderBy('ganancia_total', 'DESC')
            ->get()
            ->getResultArray();
    }



    /**
     * Buscar problemas para TomSelect
     */
    public function buscarParaTomSelect(?int $tipoId, string $query): array
    {
        $builder = $this->builder()
            ->select('id, nombre, categoria, tiempo_promedio_reparacion')
            ->where('activo', 1);

        if ($tipoId) {
            // Tiene tipo: muestra los del tipo MÁS los genéricos (sin tipo)
            $builder->groupStart()
                ->where('tipo_dispositivo_id', $tipoId)
                ->orWhere('tipo_dispositivo_id IS NULL', null, false)
                ->groupEnd();
        } else {
            // Sin tipo: solo muestra los genéricos
            $builder->where('tipo_dispositivo_id IS NULL', null, false);
        }

        if (strlen($query) >= 2) {
            $builder->like('nombre', $query);
        }

        // Cuando tiene tipo: los específicos primero, luego genéricos
        // Cuando no tiene tipo: todos son genéricos así que el order no importa mucho
        return $builder
            ->orderBy('CASE WHEN tipo_dispositivo_id IS NULL THEN 1 ELSE 0 END', 'ASC', false)
            ->orderBy('veces_reportado', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();
    }

    public function buscarActivoPorNombre(string $nombre)
    {
        return $this->where([
            'nombre' => $nombre,
            'activo' => 1
        ])->first();
    }

    public function incrementarUso(int $id): void
    {
        $this->builder()
            ->set('veces_reportado', 'veces_reportado + 1', false)
            ->where('id', $id)
            ->update();
    }
}
