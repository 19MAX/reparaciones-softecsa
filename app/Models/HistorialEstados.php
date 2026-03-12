<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialEstados extends Model
{
    protected $table            = 'historial_estados';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'dispositivo_orden_id',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
        'observacion',
        'observacion_cliente',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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

    public function obtenerHistorialCompleto()
    {
        return $this->select('historial_estados.*, 
                              do.serie_imei,
                              do.estado as dispositivo_estado,
                              td.nombre as tipo_dispositivo,
                              m.nombre as marca,
                              COALESCE(mo.nombre, do.modelo_texto) as modelo,
                              o.numero_orden,
                              c.nombres as cliente_nombre,
                              c.apellidos as cliente_apellido,
                              u.nombre as usuario_nombre, 
                              u.apellido as usuario_apellido,
                              u.rol as usuario_rol')
            ->join('dispositivos_orden do', 'do.id = historial_estados.dispositivo_orden_id')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('ordenes o', 'o.id = do.orden_id')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('usuarios u', 'u.id = historial_estados.usuario_id')
            ->orderBy('historial_estados.created_at', 'DESC')
            ->findAll();
    }
}
