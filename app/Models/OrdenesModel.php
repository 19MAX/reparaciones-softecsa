<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdenesModel extends Model
{
    protected $table = 'ordenes';
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
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];


    public function generarNumeroOrden()
    {
        $anio = date('Y');

        $ultimaOrden = $this->where('YEAR(created_at)', $anio)
            ->orderBy('id', 'DESC')
            ->first();

        $consecutivo = 1;

        if ($ultimaOrden) {
            $ultimoNumero = intval(substr($ultimaOrden['numero_orden'], -5));
            $consecutivo = $ultimoNumero + 1;
        }

        return sprintf('ORD-%s-%05d', $anio, $consecutivo);
    }
}
