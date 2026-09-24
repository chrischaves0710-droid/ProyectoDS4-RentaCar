<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentaRequest;
use App\Http\Requests\UpdateRentaRequest;
use App\Http\Resources\RentaResource;
use App\Models\Cliente;
use App\Models\Renta;
use App\Services\RentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RentaController extends Controller
{
    public function __construct(
        private RentaService $rentaService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Renta::class);

        $filtros = $request->all();

        // Un Cliente final solo puede recibir sus propias rentas.
        if ($request->user()->hasRole('Cliente')) {
            $filtros['cliente_id'] = Cliente::where(
                'correo',
                $request->user()->email
            )->value('id') ?? 0;
        }

        $rentas = $this->rentaService->listar($filtros);

        return RentaResource::collection($rentas);
    }

    public function store(StoreRentaRequest $request)
    {
        Gate::authorize('create', Renta::class);

        $renta = $this->rentaService->crear($request->validated());

        return (new RentaResource($renta))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                route('rentas.show', $renta->id)
            );
    }

    public function show($id)
    {
        $renta = $this->rentaService->obtener($id);

        Gate::authorize('view', $renta);

        return new RentaResource($renta);
    }

    public function update(UpdateRentaRequest $request, $id)
    {
        $renta = $this->rentaService->obtener($id);

        Gate::authorize('update', $renta);

        $renta = $this->rentaService->actualizar(
            $id,
            $request->validated()
        );

        return new RentaResource($renta);
    }

    public function destroy($id)
    {
        $renta = $this->rentaService->obtener($id);

        Gate::authorize('delete', $renta);

        $this->rentaService->eliminar($id);

        return response()->noContent();
    }
}