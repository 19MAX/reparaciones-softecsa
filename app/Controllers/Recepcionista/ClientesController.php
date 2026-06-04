<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use App\Models\ClienteModel;

class ClientesController extends BaseController
{

    private $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Lista de Clientes',
            'clientes' => $this->clienteModel->findAll(),
        ];

        return view('recepcionista/clientes/index', $data);
    }

    public function ver($id)
    {
        $cliente = $this->clienteModel->find($id);

        if (!$cliente) {
            return redirect()->to(base_url('recepcionista/clientes'))->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();
        $ordenes = $db->table('ordenes')
            ->where('cliente_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $data = [
            'titulo' => 'Perfil del Cliente',
            'cliente' => $cliente,
            'ordenes' => $ordenes
        ];

        return view('recepcionista/clientes/ver', $data);
    }
}