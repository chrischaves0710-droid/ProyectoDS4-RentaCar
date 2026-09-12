<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentaRequest;
use App\Http\Requests\UpdateRentaRequest;
use App\Services\RentaService;
use Illuminate\Http\Request;

class RentaController extends Controller
{
    public function __construct(
        private RentaService $rentaService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->rentaService->listar($request->all())
        ]);
    }

    public function store(StoreRentaRequest $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Renta registrada correctamente',
            'data' => $this->rentaService->crear($request->validated())
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->rentaService->obtener($id)
        ]);
    }

    public function update(UpdateRentaRequest $request, $id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Renta actualizada correctamente',
            'data' => $this->rentaService->actualizar($id, $request->validated())
        ]);
    }

    public function destroy($id)
    {
        $this->rentaService->eliminar($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Renta eliminada correctamente'
        ]);
    }
}