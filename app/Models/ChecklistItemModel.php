<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistItemModel extends Model
{
    protected $table            = 'checklist_items';
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

    public function getItemsPorTipo($tipoId)
    {
        return $this->groupStart()
                ->where('tipo_dispositivo_id', $tipoId)
                ->orWhere('tipo_dispositivo_id', null) // Items generales
            ->groupEnd()
            ->where('activo', 1)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getEstadisticasChecklist()
    {
        $db = $this->db;

        return $db->table('checklist_items ci')
            ->select('ci.nombre, ci.categoria, 
                      COUNT(cr.id) as veces_verificado,
                      SUM(CASE WHEN cr.estado = "no" THEN 1 ELSE 0 END) as veces_fallado,
                      ROUND(SUM(CASE WHEN cr.estado = "no" THEN 1 ELSE 0 END) * 100.0 / COUNT(cr.id), 2) as porcentaje_fallo')
            ->join('checklist_respuestas cr', 'cr.checklist_item_id = ci.id', 'left')
            ->where('ci.activo', 1)
            ->groupBy('ci.id')
            ->orderBy('veces_fallado', 'DESC')
            ->get()
            ->getResultArray();
    }

     /**
     * Buscar items para TomSelect
     */
    /**
     * Buscar checklist items para TomSelect
     */
    public function buscarParaTomSelect(?int $tipoId, string $query): array
    {
        $builder = $this->builder()
            ->select('id, nombre, categoria, es_critico, requiere_foto')
            ->where('activo', 1);

        if ($tipoId) {
            $builder->groupStart()
                ->where('tipo_dispositivo_id', $tipoId)
                ->orWhere('tipo_dispositivo_id IS NULL', null, false)
                ->groupEnd();
        } else {
            $builder->where('tipo_dispositivo_id IS NULL', null, false);
        }

        if (strlen($query) >= 2) {
            $builder->like('nombre', $query);
        }

        return $builder
            ->orderBy('CASE WHEN tipo_dispositivo_id = ' . (int)$tipoId . ' THEN 0 ELSE 1 END', 'ASC', false)
            ->orderBy('categoria', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->limit(20)
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
}
