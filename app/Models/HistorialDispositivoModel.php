<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialDispositivoModel extends Model
{
    protected $table = 'historial_dispositivos';
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

    // Función para obtener datos legibles (JOINs)
    public function obtenerHistorialCompleto()
    {
        return $this->select('historial_dispositivos.*, 
                              dispositivos.modelo, 
                              dispositivos.marca,
                              ordenes_trabajo.codigo_orden,
                              clientes.nombres as cliente_nombre,
                              clientes.apellidos as cliente_apellido,
                              usuarios.nombres as usuario_nombre, 
                              usuarios.apellidos as usuario_apellido,
                              usuarios.role as usuario_rol')
            // Join al dispositivo
            ->join('dispositivos', 'dispositivos.id = historial_dispositivos.dispositivo_id')
            // Join a la orden (para sacar el codigo_orden)
            ->join('ordenes_trabajo', 'ordenes_trabajo.id = dispositivos.orden_id')
            // Join al cliente (para saber de quien es)
            ->join('clientes', 'clientes.id = ordenes_trabajo.cliente_id')
            // Join al usuario que hizo la accion
            ->join('usuarios', 'usuarios.id = historial_dispositivos.usuario_id')

            ->orderBy('historial_dispositivos.created_at', 'DESC')
            ->findAll();
    }
}
