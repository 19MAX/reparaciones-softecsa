<?php

namespace App\Models;

use CodeIgniter\Model;

class MarcaModel extends Model
{
    protected $table = 'marcas';
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

    public function getMarcasPorTipo($tipoId)
    {
        return $this->where('tipo_dispositivo_id', $tipoId)
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    public function getMarcasConConteo()
    {
        $db = $this->db;

        return $db->table('marcas m')
            ->select('m.*, td.nombre as tipo_nombre, COUNT(d.id) as total_dispositivos')
            ->join('tipos_dispositivo td', 'td.id = m.tipo_dispositivo_id')
            ->join('dispositivos d', 'd.marca_id = m.id', 'left')
            ->where('m.activo', 1)
            ->groupBy('m.id')
            ->orderBy('total_dispositivos', 'DESC')
            ->get()
            ->getResultArray();
    }

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
