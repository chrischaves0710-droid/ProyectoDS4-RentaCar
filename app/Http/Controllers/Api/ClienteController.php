<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
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
        $clientes = $this->service->listar(
            $request->input('q'),
            $request->input('sortBy', 'created_at'),
            $request->input('sortDir', 'desc'),
            (int) $request->input('perPage', 15)
        );

        return ClienteResource::collection($clientes);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = $this->service->crear(
            $request->validated()
        );

        return (new ClienteResource($cliente))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                route('clientes.show', $cliente)
            );
    }

    public function show(Cliente $cliente)
    {
        return new ClienteResource(
            $this->service->obtener($cliente)
        );
    }

    public function update(
        UpdateClienteRequest $request,
        Cliente $cliente
    ) {
        $cliente = $this->service->actualizar(
            $cliente,
            $request->validated()
        );

        return new ClienteResource($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        $this->service->eliminar($cliente);

        return response()->noContent();
    }
}