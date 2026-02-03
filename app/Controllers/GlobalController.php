<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccesorioModel;
use App\Models\ChecklistItemModel;
use App\Models\DispositivoModel;
use App\Models\MarcaModel;
use App\Models\ModeloDispositivoModel;
use App\Models\ProblemasComunesModel;

class GlobalController extends BaseController
{
    protected $marcasModel;
    protected $modelosModel;
    protected $problemasModel;
    protected $accesorioModel;
    protected $checklistItemModel;
    protected $dispositivoModel;
    protected $db;

    public function __construct()
    {
        $this->marcasModel = new MarcaModel();
        $this->modelosModel = new ModeloDispositivoModel();
        $this->problemasModel = new ProblemasComunesModel();
        $this->accesorioModel = new AccesorioModel();
        $this->checklistItemModel = new ChecklistItemModel();
        $this->dispositivoModel = new DispositivoModel();

        $this->db = \Config\Database::connect();
    }

    public function buscarMarcas()
    {
        $query = $this->request->getGet('q') ?? '';
        $tipoId = $this->request->getGet('tipo') ?? '';

        $marcas = $this->marcasModel->buscar($query, $tipoId);

        return $this->response->setJSON($marcas);
    }

    public function crearMarca()
    {
        $data = $this->request->getJSON(true);

        $insertData = [
            'nombre' => $data['nombre'],
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null
        ];

        try {
            $id = $this->marcasModel->crear($insertData);

            return $this->response->setJSON([
                'status' => 'success',
                'id' => $id,
                'nombre' => $insertData['nombre']
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function buscarModelos()
    {
        $query = $this->request->getGet('q') ?? '';
        $marcaId = $this->request->getGet('marca') ?? '';

        $modelos = $this->modelosModel->buscar($query, $marcaId);

        return $this->response->setJSON($modelos);
    }

    public function crearModelo()
    {
        $data = $this->request->getJSON(true);

        $insertData = [
            'nombre' => $data['nombre'],
            'marca_id' => $data['marca_id'] ?? null
        ];

        try {
            $id = $this->modelosModel->crear($insertData);

            return $this->response->setJSON([
                'status' => 'success',
                'id' => $id,
                'nombre' => $insertData['nombre']
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar problemas comunes
     */
    public function buscarProblemasComunes()
    {
        $query = trim($this->request->getGet('q') ?? '');
        $tipoId = $this->request->getGet('tipo');

        $problemas = $this->problemasModel->buscarParaTomSelect(
            $tipoId ? (int) $tipoId : null,
            $query
        );

        // Formato exacto que espera TomSelect
        $opciones = array_map(static function ($p) {
            return [
                'value' => $p['id'],
                'text' => $p['nombre'],
                'categoria' => $p['categoria'],
                'tiempo' => $p['tiempo_promedio_reparacion'],
            ];
        }, $problemas);

        return $this->response->setJSON($opciones);
    }
    /**
     * Crear problema común inline
     */
    public function crearProblemasComun()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se recibieron datos'
            ]);
        }

        $nombre = trim($data['nombre'] ?? '');

        if ($nombre === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre es requerido'
            ]);
        }

        // Verificar duplicado
        $existente = $this->problemasModel->buscarActivoPorNombre($nombre);

        if ($existente) {
            return $this->response->setJSON([
                'status' => 'success',
                'id' => $existente['id'],
                'nombre' => $existente['nombre'],
                'categoria' => $existente['categoria'],
                'tiempo' => $existente['tiempo_promedio_reparacion']
            ]);
        }

        $insertData = [
            'nombre' => $nombre,
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null,
            'categoria' => 'hardware',
            'activo' => 1,
            'veces_reportado' => 0
        ];

        $id = $this->problemasModel->insert($insertData, true);

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $id,
            'nombre' => $nombre,
            'categoria' => 'hardware',
            'tiempo' => null
        ]);
    }
    /**
     * Incrementar contador de uso
     */
    public function registrarUsoProblema()
    {
        $id = (int) $this->request->getPost('id');

        if ($id > 0) {
            $this->problemasModel->incrementarUso($id);
        }

        return $this->response->setJSON(['status' => 'success']);
    }


    /**
     * Buscar accesorios
     */
    public function buscarAccesorios()
    {
        $query = trim($this->request->getGet('q') ?? '');
        $tipoId = $this->request->getGet('tipo');

        $accesorios = $this->accesorioModel->buscarParaTomSelect(
            $tipoId ? (int) $tipoId : null,
            $query
        );

        return $this->response->setJSON(array_map(
            fn($a) => ['value' => $a['id'], 'text' => $a['nombre']],
            $accesorios
        ));
    }


    /**
     * Crear accesorio
     */
    public function crearAccesorio()
    {
        $data = $this->request->getJSON(true);

        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre es requerido'
            ]);
        }

        $existente = $this->accesorioModel->buscarActivoPorNombre($nombre);
        if ($existente) {
            return $this->response->setJSON([
                'status' => 'success',
                'id' => $existente['id'],
                'nombre' => $existente['nombre']
            ]);
        }

        $id = $this->accesorioModel->insert([
            'nombre' => $nombre,
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null,
            'activo' => 1
        ], true);

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $id,
            'nombre' => $nombre
        ]);
    }


    /**
     * Buscar checklist items
     */
    public function buscarChecklist()
    {
        $query = trim($this->request->getGet('q') ?? '');
        $tipoId = $this->request->getGet('tipo');

        $items = $this->checklistItemModel->buscarParaTomSelect(
            $tipoId ? (int) $tipoId : null,
            $query
        );

        return $this->response->setJSON(array_map(fn($i) => [
            'value' => $i['id'],
            'text' => $i['nombre'],
            'categoria' => $i['categoria'],
            'es_critico' => $i['es_critico'],
            'requiere_foto' => $i['requiere_foto']
        ], $items));
    }


    /**
     * Crear checklist item
     */
    public function crearChecklistItem()
    {
        $data = $this->request->getJSON(true);

        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre es requerido'
            ]);
        }

        $existente = $this->checklistItemModel->buscarActivoPorNombre($nombre);
        if ($existente) {
            return $this->response->setJSON([
                'status' => 'success',
                ...$existente
            ]);
        }

        $id = $this->checklistItemModel->insert([
            'nombre' => $nombre,
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null,
            'categoria' => 'fisico',
            'es_critico' => false,
            'requiere_foto' => false,
            'activo' => 1
        ], true);

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $id,
            'nombre' => $nombre,
            'categoria' => 'fisico',
            'es_critico' => false,
            'requiere_foto' => false
        ]);
    }

    /**
     * Obtener detalles de dispositivos de una orden (para modal)
     */
    public function obtenerDispositivos($ordenId)
    {
        // Verificar sesión
        if (!session()->get('id_usuario')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesión expirada'
            ]);
        }

        try {
            // Obtener dispositivos con todos sus detalles
            $dispositivos = $this->dispositivoModel
                ->select('
                dispositivos.id,
                dispositivos.serie_imei,
                dispositivos.tipo_pass,
                dispositivos.estado_diagnostico,
                dispositivos.diagnostico_detalle,
                dispositivos.diagnostico_cliente,
                td.nombre as tipo_dispositivo,
                m.nombre as marca,
                mod.nombre as modelo,
                CONCAT(u.nombres, " ", u.apellidos) as tecnico_asignado,
                u.nombres as tecnico_nombre
            ')
                ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
                ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
                ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
                ->join('usuarios as u', 'u.id = dispositivos.tecnico_id', 'left')
                ->where('dispositivos.orden_id', $ordenId)
                ->findAll();

            if (empty($dispositivos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron dispositivos'
                ]);
            }

            // Enriquecer cada dispositivo con problemas, accesorios y checklist
            foreach ($dispositivos as &$dispositivo) {
                // Obtener problemas reportados
                $problemas = $this->db->table('dispositivo_problemas as dp')
                    ->select('pc.nombre as problema, dp.diagnostico_inicial, dp.prioridad')
                    ->join('problemas_comunes as pc', 'pc.id = dp.problema_comun_id', 'left')
                    ->where('dp.dispositivo_id', $dispositivo['id'])
                    ->get()
                    ->getResultArray();

                $dispositivo['problemas'] = $problemas;

                // Obtener accesorios
                $accesorios = $this->db->table('dispositivo_accesorios as da')
                    ->select('a.nombre as accesorio, da.estado, da.observacion')
                    ->join('accesorios as a', 'a.id = da.accesorio_id')
                    ->where('da.dispositivo_id', $dispositivo['id'])
                    ->get()
                    ->getResultArray();

                $dispositivo['accesorios'] = $accesorios;

                // Obtener checklist
                $checklist = $this->db->table('dispositivo_check as dc')
                    ->select('ci.nombre as item, dc.observacion')
                    ->join('checklist_items as ci', 'ci.id = dc.checklist_item_id')
                    ->where('dc.dispositivo_id', $dispositivo['id'])
                    ->get()
                    ->getResultArray();

                $dispositivo['checklist'] = $checklist;
            }

            return $this->response->setJSON([
                'success' => true,
                'dispositivos' => $dispositivos
            ]);

        } catch (\Exception $e) {
            log_message('error', '[OrdenController::obtenerDispositivos] ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener dispositivos: ' . $e->getMessage()
            ]);
        }
    }
}
