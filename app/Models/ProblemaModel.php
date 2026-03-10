<?php

namespace App\Models;

use CodeIgniter\Model;

class ProblemaModel extends Model
{
    protected $table = 'problemas';
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


    /**
     * Buscar problemas para TomSelect
     */
    public function buscarParaTomSelect(?int $tipoId, string $query, ?int $modeloId = null): array
    {
        $builder = $this->db->table('problemas p');

        $builder->select([
            'p.id',
            'p.nombre',
            'p.tiempo_reparacion_horas',
            'COALESCE(pb_modelo.precio_mano_obra, pb_generico.precio_mano_obra) AS precio_mano_obra',
            'COALESCE(pb_modelo.precio_repuesto, pb_generico.precio_repuesto) AS precio_repuesto'
        ], false);

        /*
        |--------------------------------------------------------------------------
        | JOIN precio específico por modelo
        |--------------------------------------------------------------------------
        */
        if ($modeloId !== null) {
            $builder->join(
                'precios_base pb_modelo',
                'pb_modelo.problema_id = p.id AND pb_modelo.modelo_id = ' . (int) $modeloId,
                'left',
                false
            );
        } else {
            $builder->join(
                'precios_base pb_modelo',
                'pb_modelo.problema_id = p.id AND pb_modelo.modelo_id IS NULL',
                'left',
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JOIN precio genérico
        |--------------------------------------------------------------------------
        */
        $builder->join(
            'precios_base pb_generico',
            'pb_generico.problema_id = p.id AND pb_generico.modelo_id IS NULL',
            'left',
            false
        );

        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */
        $builder->where('p.activo', 1);

        if ($tipoId !== null) {
            $builder->where('p.tipo_dispositivo_id', $tipoId);
        } else {
            $builder->where('p.tipo_dispositivo_id IS NULL', null, false);
        }

        if (strlen($query) >= 2) {
            $builder->like('p.nombre', $query);
        }

        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        */
        $builder->orderBy("
        CASE
            WHEN pb_modelo.id IS NOT NULL THEN 0
            WHEN pb_generico.id IS NOT NULL THEN 1
            ELSE 2
        END
    ", '', false);

        $builder->orderBy('p.nombre', 'ASC');

        $builder->limit(15);

        return $builder->get()->getResultArray();
    }

    public function buscarActivoPorNombre(string $nombre)
    {
        return $this->where([
            'nombre' => $nombre,
            'activo' => 1
        ])->first();
    }
}
