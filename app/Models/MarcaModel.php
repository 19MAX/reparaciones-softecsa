<?php

namespace App\Models;

use CodeIgniter\Model;

class MarcaModel extends Model
{
    protected $table            = 'marcas';
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


    public function buscar($query = '', $tipoId = '')
    {
        $builder = $this->builder();

        $builder->where('activo', 1);

        if (!empty($tipoId)) {
            $builder->where('tipo_dispositivo_id', $tipoId);
        }

        if (!empty($query)) {
            $builder->groupStart()
                ->like('nombre', $query)
                ->groupEnd();
        }

        return $builder->limit(50)->get()->getResultArray();
    }

    public function crear(array $data)
    {
        $this->insert($data);
        return $this->getInsertID();
    }
}
