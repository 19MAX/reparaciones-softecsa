<?php

namespace App\Models;

use CodeIgniter\Model;

class RepuestoModel extends Model
{
    protected $table            = 'repuestos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'nombre' => 'required|min_length[3]',
        'valor'  => 'required|numeric',
    ];
}
