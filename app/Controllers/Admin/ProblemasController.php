<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProblemaModel;
use App\Models\PreciosBaseModel;
use App\Models\TipoDispositivoModel;
use App\Models\MarcaModel;
use App\Models\ModeloModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProblemasController extends BaseController
{
    public $problemaModel;
    public $preciosBaseModel;
    public $tipoDispositivoModel;
    public $validation;

    public function __construct()
    {
        $this->problemaModel = new ProblemaModel();
        $this->preciosBaseModel = new PreciosBaseModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $data = [
            'problemas' => $this->problemaModel->getProblemasConPrecios(),
            'tiposDispositivo' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),
            'titulo' => 'Problemas y Precios Base',
        ];

        return view('admin/problemas/index', $data);
    }

    public function crear()
    {
        $data = [
            'tipo_dispositivo_id'     => $this->request->getPost('tipo_dispositivo_id'),
            'nombre'                  => $this->request->getPost('nombre'),
            'descripcion'             => $this->request->getPost('descripcion'),
            'tiempo_reparacion_horas' => $this->request->getPost('tiempo_reparacion_horas'),
            'activo'                  => 1,
        ];

        $rules = [
            'tipo_dispositivo_id'     => 'required|integer',
            'nombre'                  => 'required|max_length[200]',
            'tiempo_reparacion_horas' => 'required|numeric|greater_than[0]',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return redirectView('admin/problemas', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        $this->problemaModel->insert($data);
        $problemaId = $this->problemaModel->getInsertID();

        // Crear precio base genérico (modelo_id = NULL)
        $precioData = [
            'problema_id'      => $problemaId,
            'modelo_id'        => null,
            'precio_mano_obra' => $this->request->getPost('precio_mano_obra') ?? 0,
            'precio_repuesto'  => $this->request->getPost('precio_repuesto') ?? 0,
        ];

        $this->preciosBaseModel->insert($precioData);

        return redirectView('admin/problemas', null, [['Problema creado correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->problemaModel->find($id)) {
                return redirectView('admin/problemas', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $data = [
                'tipo_dispositivo_id'     => $this->request->getPost('tipo_dispositivo_id'),
                'nombre'                  => $this->request->getPost('nombre'),
                'descripcion'             => $this->request->getPost('descripcion'),
                'tiempo_reparacion_horas' => $this->request->getPost('tiempo_reparacion_horas'),
            ];

            $data['activo'] = $this->request->getPost('activo') ? 1 : 0;

            $rules = [
                'tipo_dispositivo_id'     => 'required|integer',
                'nombre'                  => 'required|max_length[200]',
                'tiempo_reparacion_horas' => 'required|numeric|greater_than[0]',
            ];

            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView('admin/problemas', $this->validation, [['Errores de validación', 'error', 'top-end']]);
            }

            $this->problemaModel->update($id, $data);

            // Actualizar o crear precio base genérico
            $precioExistente = $this->preciosBaseModel
                ->where('problema_id', $id)
                ->where('modelo_id IS NULL', null, false)
                ->first();

            $precioData = [
                'precio_mano_obra' => $this->request->getPost('precio_mano_obra') ?? 0,
                'precio_repuesto'  => $this->request->getPost('precio_repuesto') ?? 0,
            ];

            if ($precioExistente) {
                $this->preciosBaseModel->update($precioExistente['id'], $precioData);
            } else {
                $precioData['problema_id'] = $id;
                $precioData['modelo_id'] = null;
                $this->preciosBaseModel->insert($precioData);
            }

            return redirectView('admin/problemas', null, [['Problema actualizado exitosamente', 'success', 'top-end']]);

        } catch (\Exception $e) {
            return redirectView('admin/problemas', null, [['Error al actualizar: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->problemaModel->find($id)) {
                return redirectView('admin/problemas', null, [['El registro no existe', 'error', 'top-end']]);
            }

            // Eliminar precios base asociados primero
            $this->preciosBaseModel->where('problema_id', $id)->delete();

            $this->problemaModel->delete($id);

            return redirectView('admin/problemas', null, [['Problema eliminado exitosamente', 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/problemas', null, [['Error al eliminar: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    // ─── Precios por Modelo (AJAX) ────────────────────────────────────

    /**
     * Obtener precios específicos por modelo de un problema
     */
    public function getPreciosModelo($problemaId)
    {
        $precios = $this->preciosBaseModel
            ->select('precios_base.*, m.nombre AS modelo_nombre, ma.nombre AS marca_nombre')
            ->join('modelos m', 'm.id = precios_base.modelo_id', 'left')
            ->join('marcas ma', 'ma.id = m.marca_id', 'left')
            ->where('precios_base.problema_id', $problemaId)
            ->where('precios_base.modelo_id IS NOT NULL', null, false)
            ->orderBy('ma.nombre', 'ASC')
            ->orderBy('m.nombre', 'ASC')
            ->findAll();

        return $this->response->setJSON(['success' => true, 'precios' => $precios]);
    }

    /**
     * Obtener marcas por tipo de dispositivo (AJAX)
     */
    public function getMarcasPorTipo($tipoId)
    {
        $marcaModel = new MarcaModel();
        $marcas = $marcaModel->where('tipo_dispositivo_id', $tipoId)->where('activo', 1)->findAll();

        return $this->response->setJSON($marcas);
    }

    /**
     * Obtener modelos por marca (AJAX)
     */
    public function getModelosPorMarca($marcaId)
    {
        $modeloModel = new ModeloModel();
        $modelos = $modeloModel->where('marca_id', $marcaId)->where('activo', 1)->findAll();

        return $this->response->setJSON($modelos);
    }

    /**
     * Guardar precio específico por modelo (AJAX)
     */
    public function guardarPrecioModelo()
    {
        try {
            $problemaId    = $this->request->getPost('problema_id');
            $modeloId      = $this->request->getPost('modelo_id');
            $precioManoObra = $this->request->getPost('precio_mano_obra') ?? 0;
            $precioRepuesto = $this->request->getPost('precio_repuesto') ?? 0;

            if (!$problemaId || !$modeloId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Problema y Modelo son requeridos']);
            }

            // Verificar si ya existe un precio para este problema+modelo
            $existe = $this->preciosBaseModel
                ->where('problema_id', $problemaId)
                ->where('modelo_id', $modeloId)
                ->first();

            $data = [
                'precio_mano_obra' => $precioManoObra,
                'precio_repuesto'  => $precioRepuesto,
            ];

            if ($existe) {
                $this->preciosBaseModel->update($existe['id'], $data);
            } else {
                $data['problema_id'] = $problemaId;
                $data['modelo_id']   = $modeloId;
                $this->preciosBaseModel->insert($data);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Precio guardado correctamente']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar precio específico por modelo (AJAX)
     */
    public function eliminarPrecioModelo()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id) {
                return $this->response->setJSON(['success' => false, 'message' => 'ID requerido']);
            }

            $precio = $this->preciosBaseModel->find($id);

            if (!$precio || $precio['modelo_id'] === null) {
                return $this->response->setJSON(['success' => false, 'message' => 'No se puede eliminar el precio genérico']);
            }

            $this->preciosBaseModel->delete($id);

            return $this->response->setJSON(['success' => true, 'message' => 'Precio eliminado']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
