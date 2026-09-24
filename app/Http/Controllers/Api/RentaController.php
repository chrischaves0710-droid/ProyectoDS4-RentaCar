<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentaRequest;
use App\Http\Requests\UpdateRentaRequest;
use App\Http\Resources\RentaResource;
use App\Services\RentaService;
use Illuminate\Http\Request;

class RentaController extends Controller
{
    public function __construct(
        private RentaService $rentaService
    ) {}

    public function index(Request $request)
    {
        $rentas = $this->rentaService->listar(
            $request->all()
        );

        return RentaResource::collection($rentas);
    }

    public function store(StoreRentaRequest $request)
    {
        $renta = $this->rentaService->crear(
            $request->validated()
        );

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

        return new RentaResource($renta);
    }

    public function update(
        UpdateRentaRequest $request,
        $id
    ) {
        $renta = $this->rentaService->actualizar(
            $id,
            $request->validated()
        );

        return new RentaResource($renta);
    }

    public function destroy($id)
    {
        $this->rentaService->eliminar($id);

        return response()->noContent();
    }
}