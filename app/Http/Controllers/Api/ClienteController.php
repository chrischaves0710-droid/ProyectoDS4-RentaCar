<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Services\ClienteService;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct(
        private ClienteService $service
    ) {
    }

    public function index(Request $request)
    {
        return $this->service->listar(
            $request->input('q'),
            $request->input('sortBy', 'created_at'),
            $request->input('sortDir', 'desc'),
            (int) $request->input('perPage', 15)
        );
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = $this->service->crear($request->validated());

        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return $this->service->obtener($cliente);
    }

    public function update(
        UpdateClienteRequest $request,
        Cliente $cliente
    ) {
        return $this->service->actualizar(
            $cliente,
            $request->validated()
        );
    }

    public function destroy(Cliente $cliente)
    {
        $this->service->eliminar($cliente);

        return response()->noContent();
    }
}