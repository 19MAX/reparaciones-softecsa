<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccesoriosCatalogoModel;
use App\Models\DetallesCatalogoModel;
use App\Models\DispositivoModel;
use App\Models\MarcaModel;
use App\Models\ModeloModel;
use App\Models\ProblemaModel;

class GlobalController extends BaseController
{
    protected $marcasModel;
    protected $modelosModel;
    protected $problemasModel;
    protected $accesorioModel;
    protected $detallesModel;
    protected $dispositivoModel;
    protected $db;

    public function __construct()
    {
        $this->marcasModel = new MarcaModel();
        $this->modelosModel = new ModeloModel();
        $this->problemasModel = new ProblemaModel();
        $this->accesorioModel = new AccesoriosCatalogoModel();
        $this->detallesModel = new DetallesCatalogoModel();
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
        $modeloId = $this->request->getGet('modelo');

        $problemas = $this->problemasModel->buscarParaTomSelect(
            $tipoId ? (int) $tipoId : null,
            $query,
            $modeloId ? (int) $modeloId : null
        );

        $opciones = array_map(static function ($p) {
            $mobraObra = (float) ($p['precio_mano_obra'] ?? 0);
            $repuesto = (float) ($p['precio_repuesto'] ?? 0);
            $total = $mobraObra + $repuesto;

            // Texto con precio si existe, sin precio si no hay registro
            $text = $total > 0
                ? "{$p['nombre']} $" . number_format($total, 2)
                : $p['nombre'];

            return [
                'value' => $p['id'],
                'text' => $text,
                'nombre' => $p['nombre'],          // nombre limpio para guardar
                'tiempo' => $p['tiempo_reparacion_horas'],
                'precio_mano_obra' => $mobraObra,
                'precio_repuesto' => $repuesto,
                'precio_total' => $total,
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
        $tipoId = $data['tipo_dispositivo_id'] ?? null;

        if (empty($tipoId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Debe seleccionar un tipo de dispositivo'
            ]);
        }

        // Verificar duplicado
        $existente = $this->problemasModel->buscarActivoPorNombre($nombre);

        if ($existente) {
            return $this->response->setJSON([
                'status' => 'success',
                'id' => $existente['id'],
                'nombre' => $existente['nombre'],
                'tiempo' => $existente['tiempo_reparacion_horas']
            ]);
        }

        $insertData = [
            'nombre' => $nombre,
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null,
            'activo' => 1,
        ];

        $id = $this->problemasModel->insert($insertData, true);

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $id,
            'nombre' => $nombre,
            'tiempo' => null
        ]);
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
     * Buscar detallles items
     */
    public function buscarDetalles()
    {
        $query = trim($this->request->getGet('q') ?? '');
        $tipoId = $this->request->getGet('tipo');

        $items = $this->detallesModel->buscarParaTomSelect(
            $tipoId ? (int) $tipoId : null,
            $query
        );

        return $this->response->setJSON(array_map(fn($i) => [
            'value' => $i['id'],
            'text' => $i['nombre'],
        ], $items));
    }


    /**
     * Crear detallles item
     */
    public function crearDetalles()
    {
        $data = $this->request->getJSON(true);

        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre es requerido'
            ]);
        }

        $existente = $this->detallesModel->buscarActivoPorNombre($nombre);
        if ($existente) {
            return $this->response->setJSON([
                'status' => 'success',
                ...$existente
            ]);
        }

        $id = $this->detallesModel->insert([
            'nombre' => $nombre,
            'tipo_dispositivo_id' => $data['tipo_dispositivo_id'] ?? null,
            'activo' => 1
        ], true);

        return $this->response->setJSON([
            'status' => 'success',
            'id' => $id,
            'nombre' => $nombre,
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
