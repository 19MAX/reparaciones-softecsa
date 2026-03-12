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
    protected $useTimestamps = true;
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

    /**
     * Recalcula el estado de la orden basado en el estado de sus dispositivos
     */
    public function recalcularEstado(int $ordenId)
    {
        $db = \Config\Database::connect();
        
        // Obtener todos los dispositivos de la orden
        $dispositivos = $db->table('dispositivos_orden')
            ->where('orden_id', $ordenId)
            ->get()->getResultArray();

        if (empty($dispositivos)) {
            return;
        }

        $total = count($dispositivos);
        $estados = array_column($dispositivos, 'estado');
        $counts = array_count_values($estados);

        $nuevoEstado = 'pendiente';

        // 1. Si TODOS están entregados -> entregado
        if (($counts['entregado'] ?? 0) === $total) {
            $nuevoEstado = 'entregado';
        }
        // 2. Si TODOS están listos o entregados (pero no todos entregados) -> listo
        elseif (($counts['listo'] ?? 0) + ($counts['entregado'] ?? 0) === $total) {
            $nuevoEstado = 'listo';
        }
        // 3. Si hay al menos uno en proceso, listo o pausado -> en_proceso
        elseif (
            ($counts['en_proceso'] ?? 0) > 0 || 
            ($counts['listo'] ?? 0) > 0 || 
            ($counts['pausado'] ?? 0) > 0
        ) {
            $nuevoEstado = 'en_proceso';
        }
        // 4. Si hay cancelados, hay que ver si los demás están pendientes
        elseif (($counts['cancelado'] ?? 0) === $total) {
            $nuevoEstado = 'cancelado';
        }
        // De lo contrario queda como pendiente

        // Actualizar la orden
        $this->update($ordenId, ['estado' => $nuevoEstado]);
        
        return $nuevoEstado;
    }
}
