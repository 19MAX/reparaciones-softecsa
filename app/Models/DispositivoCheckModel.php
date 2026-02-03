<?php

namespace App\Models;

use CodeIgniter\Model;

class DispositivoCheckModel extends Model
{
    protected $table = 'dispositivo_check';
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
     * Obtener todos los items del checklist de un dispositivo con información completa
     */
    public function getChecklistByDispositivo($dispositivoId)
    {
        return $this->select('dispositivo_check.*, checklist_items.nombre, checklist_items.categoria, checklist_items.es_critico, checklist_items.requiere_foto')
            ->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->orderBy('checklist_items.es_critico', 'DESC')
            ->orderBy('checklist_items.categoria', 'ASC')
            ->findAll();
    }

    /**
     * Obtener items críticos del checklist
     */
    public function getItemsCriticos($dispositivoId)
    {
        return $this->select('dispositivo_check.*, checklist_items.nombre, checklist_items.categoria')
            ->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->where('checklist_items.es_critico', 1)
            ->findAll();
    }

    /**
     * Obtener items que requieren foto
     */
    public function getItemsConFoto($dispositivoId)
    {
        return $this->select('dispositivo_check.*, checklist_items.nombre')
            ->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->where('checklist_items.requiere_foto', 1)
            ->findAll();
    }

    /**
     * Actualizar observación de un item del checklist
     */
    public function actualizarObservacion($checkId, $observacion)
    {
        return $this->update($checkId, [
            'observacion' => $observacion,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Contar items del checklist de un dispositivo
     */
    public function contarItems($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)->countAllResults();
    }

    /**
     * Contar items críticos pendientes de revisión
     */
    public function contarCriticosPendientes($dispositivoId)
    {
        return $this->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->where('checklist_items.es_critico', 1)
            ->where('dispositivo_check.observacion IS NULL')
            ->countAllResults();
    }

    /**
     * Obtener items agrupados por categoría
     */
    public function getItemsPorCategoria($dispositivoId)
    {
        $items = $this->select('dispositivo_check.*, checklist_items.nombre, checklist_items.categoria, checklist_items.es_critico, checklist_items.requiere_foto')
            ->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->findAll();

        // Agrupar por categoría
        $agrupados = [];
        foreach ($items as $item) {
            $categoria = $item['categoria'] ?? 'general';
            if (!isset($agrupados[$categoria])) {
                $agrupados[$categoria] = [];
            }
            $agrupados[$categoria][] = $item;
        }

        return $agrupados;
    }

    /**
     * Eliminar todos los items del checklist de un dispositivo
     */
    public function eliminarPorDispositivo($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)->delete();
    }

    /**
     * Verificar si un item ya está en el checklist del dispositivo
     */
    public function existeItem($dispositivoId, $checklistItemId)
    {
        return $this->where([
            'dispositivo_id' => $dispositivoId,
            'checklist_item_id' => $checklistItemId
        ])->first() !== null;
    }

    /**
     * Agregar múltiples items al checklist de un dispositivo
     */
    public function agregarMultiples($dispositivoId, array $checklistItemsIds)
    {
        $data = [];
        foreach ($checklistItemsIds as $itemId) {
            if (!$this->existeItem($dispositivoId, $itemId)) {
                $data[] = [
                    'dispositivo_id' => $dispositivoId,
                    'checklist_item_id' => $itemId,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($data)) {
            return $this->insertBatch($data);
        }

        return true;
    }

    /**
     * Obtener resumen del checklist (completado vs pendiente)
     */
    public function getResumenChecklist($dispositivoId)
    {
        $total = $this->where('dispositivo_id', $dispositivoId)->countAllResults();

        $completados = $this->where('dispositivo_id', $dispositivoId)
            ->where('observacion IS NOT NULL')
            ->countAllResults();

        $criticos = $this->join('checklist_items', 'checklist_items.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->where('checklist_items.es_critico', 1)
            ->countAllResults();

        return [
            'total' => $total,
            'completados' => $completados,
            'pendientes' => $total - $completados,
            'criticos' => $criticos,
            'porcentaje_completado' => $total > 0 ? round(($completados / $total) * 100, 2) : 0
        ];
    }
}
