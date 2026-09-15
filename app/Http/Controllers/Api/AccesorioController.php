<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AccesorioException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccesorioRequest;
use App\Http\Requests\UpdateAccesorioRequest;
use App\Models\Accesorio;
use App\Models\Renta;
use App\Services\AccesorioService;
use Illuminate\Http\Request;

class AccesorioController extends Controller
{
    public function __construct(protected AccesorioService $accesorioService)
    {
    }

    public function index(Request $request)
    {
        return $this->accesorioService->listar($request->all());
    }

    public function store(StoreAccesorioRequest $request)
    {
        $accesorio = $this->accesorioService->crear($request->validated());
        return response()->json(['message' => 'Accesorio creado con éxito', 'data' => $accesorio], 201);
    }

    public function show(Accesorio $accesorio)
    {
        return response()->json($accesorio, 200);
    }

    public function update(UpdateAccesorioRequest $request, Accesorio $accesorio)
    {
        $accesorio = $this->accesorioService->actualizar($accesorio, $request->validated());
        return response()->json(['message' => 'Accesorio actualizado con éxito', 'data' => $accesorio], 200);
    }

    public function destroy(Accesorio $accesorio)
    {
        $this->accesorioService->eliminar($accesorio);
        return response()->json(['message' => 'Accesorio eliminado con éxito'], 200);
    }

    // Método para agregar un accesorio a una renta
    public function agregarARenta(Request $request, Accesorio $accesorio, Renta $renta)
    {
        $validated = $request->validate(['cantidad' => 'required|integer|min:1']);
        $renta = $this->accesorioService->agregarARenta($accesorio, $renta, $validated['cantidad']);
        return response()->json(['message' => 'Accesorio agregado a la renta', 'data' => $renta], 200);
    }
}