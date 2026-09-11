<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de clientes.
     */
    public function index(Request $request)
    {
        $q = $request->input('q');

        $clientes = Cliente::when($q, function ($query, $q) {
            return $query->where('nombre1', 'like', "%$q%")
                         ->orWhere('cedula', 'like', "%$q%")
                         ->orWhere('correo', 'like', "%$q%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate(25);

        return $clientes;
    }

    /**
     * Guarda un nuevo cliente.
     */
    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return $cliente;
    }

    /**
     * Muestra la información de un cliente.
     */
    public function show(Cliente $cliente)
    {
        return $cliente;
    }

    /**
     * Actualiza la información de un cliente.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return $cliente;
    }

    /**
     * Elimina un cliente.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente'
        ]);
    }
}