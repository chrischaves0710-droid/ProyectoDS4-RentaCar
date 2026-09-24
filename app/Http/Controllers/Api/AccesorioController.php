<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccesorioRequest;
use App\Http\Requests\UpdateAccesorioRequest;
use App\Http\Resources\AccesorioResource;
use App\Models\Accesorio;
use App\Models\Renta;
use App\Services\AccesorioService;
use Illuminate\Http\Request;

class AccesorioController extends Controller
{
    public function __construct(protected AccesorioService $accesorioService)
    {
    }

    /**
     * GET /api/accesorios
     * Listado paginado de accesorios (200 OK)
     */
    public function index(Request $request)
    {
        $accesorios = $this->accesorioService->listar($request->all());

        return AccesorioResource::collection($accesorios);
    }

    /**
     * POST /api/accesorios
     * Retorna 201 Created con encabezado Location
     */
    public function store(StoreAccesorioRequest $request)
    {
        $accesorio = $this->accesorioService->crear($request->validated());

        return (new AccesorioResource($accesorio))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('accesorios.show', ['accesorio' => $accesorio->id]));
    }

    /**
     * GET /api/accesorios/{accesorio}
     * Detalle de un accesorio (200 OK)
     */
    public function show(Accesorio $accesorio)
    {
        $accesorio = $this->accesorioService->mostrar($accesorio);

        return new AccesorioResource($accesorio);
    }

    /**
     * PUT/PATCH /api/accesorios/{accesorio}
     * Actualiza el accesorio (200 OK)
     */
    public function update(UpdateAccesorioRequest $request, Accesorio $accesorio)
    {
        $accesorioActualizado = $this->accesorioService->actualizar($accesorio, $request->validated());

        return new AccesorioResource($accesorioActualizado);
    }

    /**
     * DELETE /api/accesorios/{accesorio}
     * Elimina el recurso (204 No Content)
     */
    public function destroy(Accesorio $accesorio)
    {
        $this->accesorioService->eliminar($accesorio);

        return response()->json(null, 204);
    }

    /**
     * POST /api/rentas/{renta}/accesorios/{accesorio}
     * Asocia un accesorio a una renta (200 OK)
     */
    public function agregarARenta(Request $request, Renta $renta, Accesorio $accesorio)
    {
        $validated = $request->validate(['cantidad' => 'required|integer|min:1']);
        
        $rentaActualizada = $this->accesorioService->agregarARenta($accesorio, $renta, $validated['cantidad']);

        return response()->json($rentaActualizada, 200);
    }
}