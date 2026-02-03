<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialClienteModel extends Model
{
    protected $table            = 'historial_dispositivos';
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
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

     /**
     * Obtener historial completo de un dispositivo
     * Ordenado de más reciente a más antiguo
     */
    public function getHistorialDispositivo($dispositivoId, $soloVisible = false)
    {
        $db = $this->db;

        $builder = $db->table('historial_cliente hc')
            ->select('hc.*, u.nombres as usuario_nombre, u.apellidos as usuario_apellido, u.role')
            ->join('usuarios u', 'u.id = hc.usuario_id')
            ->where('hc.dispositivo_id', $dispositivoId);

        if ($soloVisible) {
            $builder->where('hc.es_visible_cliente', true);
        }

        return $builder->orderBy('hc.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener último estado registrado de un dispositivo
     */
    public function getUltimoEstado($dispositivoId)
    {
        return $this->where('dispositivo_id', $dispositivoId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Registrar cambio de estado (helper method)
     */
    public function registrarCambio($dispositivoId, $usuarioId, $estadoAnterior, $estadoNuevo, $comentario, $visibleCliente = true)
    {
        return $this->insert([
            'dispositivo_id' => $dispositivoId,
            'usuario_id' => $usuarioId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $comentario,
            'es_visible_cliente' => $visibleCliente
        ]);
    }

    /**
     * Obtener resumen de cambios de estado para reportes
     */
    public function getResumenCambios($fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->select('estado_nuevo, COUNT(*) as total')
            ->groupBy('estado_nuevo')
            ->orderBy('total', 'DESC');

        if ($fechaInicio && $fechaFin) {
            $builder->where('created_at >=', $fechaInicio)
                    ->where('created_at <=', $fechaFin);
        }

        return $builder->findAll();
    }

    /**
     * Obtener actividad de un usuario (cuántos cambios registró)
     */
    public function getActividadUsuario($usuarioId, $fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->select('DATE(created_at) as fecha, COUNT(*) as cambios')
            ->where('usuario_id', $usuarioId)
            ->groupBy('DATE(created_at)')
            ->orderBy('fecha', 'DESC');

        if ($fechaInicio && $fechaFin) {
            $builder->where('created_at >=', $fechaInicio)
                    ->where('created_at <=', $fechaFin);
        }

        return $builder->findAll();
    }

    /**
     * Obtener tiempo promedio entre estados
     * Ej: ¿Cuánto tarda en promedio de "en_diagnostico" a "diagnosticado"?
     */
    public function getTiempoPromedioEntreEstados($estadoInicio, $estadoFin)
    {
        $db = $this->db;

        $query = "
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, h1.created_at, h2.created_at)) as horas_promedio,
                AVG(TIMESTAMPDIFF(MINUTE, h1.created_at, h2.created_at)) as minutos_promedio
            FROM historial_cliente h1
            INNER JOIN historial_cliente h2 
                ON h1.dispositivo_id = h2.dispositivo_id 
                AND h2.created_at > h1.created_at
            WHERE h1.estado_nuevo = ?
            AND h2.estado_nuevo = ?
            AND NOT EXISTS (
                SELECT 1 FROM historial_cliente h3 
                WHERE h3.dispositivo_id = h1.dispositivo_id 
                AND h3.created_at > h1.created_at 
                AND h3.created_at < h2.created_at
            )
        ";

        return $db->query($query, [$estadoInicio, $estadoFin])->getRowArray();
    }
}
