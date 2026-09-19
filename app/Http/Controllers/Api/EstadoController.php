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
        $query = Estado::query();

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', $request->nombre . '%');
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        if (!in_array($sort, ['id', 'nombre'])) {
            $sort = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $perPage = max(1, min($request->integer('per_page', 15), 50));

        $paginator = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => EstadoResource::collection($paginator->items())->resolve($request),
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
        $estado = $this->estadoService->crear($request->validated());

        return response()->json([
            'data' => new EstadoResource($estado),
        ], 201)->header(
            'Location',
            route('estados.show', ['estado' => $estado->id])
        );
    }

    public function show(Estado $estado)
    {
        return response()->json([
            'data' => new EstadoResource($estado),
        ], 200);
    }

    public function update(UpdateEstadoRequest $request, Estado $estado)
    {
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