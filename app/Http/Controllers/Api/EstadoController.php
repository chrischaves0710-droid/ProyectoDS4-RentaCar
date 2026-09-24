<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstadoRequest;
use App\Http\Requests\UpdateEstadoRequest;
use App\Http\Resources\EstadoResource;
use App\Models\Estado;
use App\Services\EstadoService;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function __construct(protected EstadoService $estadoService)
    {
    }

    public function index(Request $request)
    {
        $paginator = $this->estadoService->listar([
            'nombre' => $request->input('nombre'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
            'per_page' => $request->integer('per_page', 15),
        ]);

        return response()->json([
            'data' => EstadoResource::collection(
                $paginator->items()
            )->resolve($request),

            'meta' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'total_records' => $paginator->total(),
            ],

            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    public function store(StoreEstadoRequest $request)
    {
        $estado = $this->estadoService->crear(
            $request->validated()
        );

        return response()->json([
            'data' => new EstadoResource($estado),
        ], 201)->header(
            'Location',
            route('estados.show', [
                'estado' => $estado->id
            ])
        );
    }

    public function show(Estado $estado)
    {
        $estado = $this->estadoService->mostrar($estado);

        return response()->json([
            'data' => new EstadoResource($estado),
        ], 200);
    }

    public function update(
        UpdateEstadoRequest $request,
        Estado $estado
    ) {
        $estado = $this->estadoService->actualizar(
            $estado,
            $request->validated()
        );

        return response()->json([
            'data' => new EstadoResource($estado),
        ], 200);
    }

    public function destroy(Estado $estado)
    {
        $this->estadoService->eliminar($estado);

        return response()->noContent();
    }
}