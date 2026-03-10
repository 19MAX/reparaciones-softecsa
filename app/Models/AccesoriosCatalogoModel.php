<?php

namespace App\Models;

use CodeIgniter\Model;

class AccesoriosCatalogoModel extends Model
{
    protected $table            = 'accesorios_catalogo';
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

    public function buscarParaTomSelect(?int $tipoId, string $query): array
    {
        $builder = $this->builder()
            ->select('id, nombre')
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
