<?php

namespace App\Models;

use CodeIgniter\Model;

class DispositivoAccesorioModel extends Model
{
    protected $table = 'dispositivo_accesorios';
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

    /**
     * Obtener todos los accesorios de un dispositivo con información completa
     */
    public function getAccesoriosByDispositivo($dispositivoId)
    {
        return $this->select('dispositivo_accesorios.*, accesorios.nombre as accesorio_nombre, accesorios.descripcion')
            ->join('accesorios', 'accesorios.id = dispositivo_accesorios.accesorio_id')
            ->where('dispositivo_accesorios.dispositivo_id', $dispositivoId)
            ->findAll();
    }

    /**
     * Actualizar el estado de un accesorio
     */
    public function actualizarEstado($accesorioDispositivoId, $nuevoEstado, $observacion = null)
    {
        $data = [
            'estado' => $nuevoEstado,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($observacion !== null) {
            $data['observacion'] = $observacion;
        }

        return $this->update($accesorioDispositivoId, $data);
    }

    /**
     * Obtener accesorios en mal estado de un dispositivo
     */
    public function getAccesoriosMalEstado($dispositivoId)
    {
        return $this->select('dispositivo_accesorios.*, accesorios.nombre as accesorio_nombre')
            ->join('accesorios', 'accesorios.id = dispositivo_accesorios.accesorio_id')
            ->where('dispositivo_accesorios.dispositivo_id', $dispositivoId)
            ->whereIn('dispositivo_accesorios.estado', ['malo', 'regular'])
            ->findAll();
    }

    /**
     * Contar accesorios de un dispositivo
     */
    public function contarAccesorios($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)->countAllResults();
    }

    /**
     * Eliminar todos los accesorios de un dispositivo
     */
    public function eliminarPorDispositivo($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)->delete();
    }

    /**
     * Verificar si un accesorio ya está asociado al dispositivo
     */
    public function existeAccesorio($dispositivoId, $accesorioId)
    {
        return $this->where([
            'dispositivo_id' => $dispositivoId,
            'accesorio_id' => $accesorioId
        ])->first() !== null;
    }

    /**
     * Agregar múltiples accesorios a un dispositivo
     */
    public function agregarMultiples($dispositivoId, array $accesoriosIds, $estadoDefault = 'bueno')
    {
        $data = [];
        foreach ($accesoriosIds as $accesorioId) {
            if (!$this->existeAccesorio($dispositivoId, $accesorioId)) {
                $data[] = [
                    'dispositivo_id' => $dispositivoId,
                    'accesorio_id' => $accesorioId,
                    'estado' => $estadoDefault,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($data)) {
            return $this->insertBatch($data);
        }

        return true;
    }
}
