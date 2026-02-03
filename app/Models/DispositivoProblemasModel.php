<?php

namespace App\Models;

use CodeIgniter\Model;

class DispositivoProblemasModel extends Model
{
    protected $table            = 'dispositivo_problemas';
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

    public function getProblemasPorDispositivo($dispositivoId)
    {
        $db = $this->db;

        return $db->table('dispositivo_problemas dp')
            ->select('dp.*, pc.nombre as problema_nombre, pc.categoria')
            ->join('problemas_comunes pc', 'pc.id = dp.problema_comun_id', 'left')
            ->where('dp.dispositivo_id', $dispositivoId)
            ->get()
            ->getResultArray();
    }
}
