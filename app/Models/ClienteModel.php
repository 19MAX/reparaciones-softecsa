<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
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

    public function buscarPorCedula($cedula)
    {
        return $this->where('cedula', $cedula)->first();
    }

    public function getClientesConOrdenes()
    {
        return $this->select('clientes.*, COUNT(ordenes_trabajo.id) as total_ordenes')
            ->join('ordenes_trabajo', 'ordenes_trabajo.cliente_id = clientes.id', 'left')
            ->groupBy('clientes.id')
            ->findAll();
    }

    public function getHistorialCliente($clienteId)
    {
        $db = $this->db;

        return $db->table('ordenes_trabajo ot')
            ->select('ot.*, COUNT(d.id) as total_dispositivos')
            ->join('dispositivos d', 'd.orden_id = ot.id', 'left')
            ->where('ot.cliente_id', $clienteId)
            ->groupBy('ot.id')
            ->orderBy('ot.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

}
