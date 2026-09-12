<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstadoRequest;
use App\Http\Requests\UpdateEstadoRequest;
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

        return $query->orderBy($sort, $direction)
            ->paginate(min($request->integer('per_page', 15), 50));
    }

    public function store(StoreEstadoRequest $request)
    {
        return response()->json(
            $this->estadoService->crear($request->validated()),
            201
        );
    }

    public function show(Estado $estado)
    {
        return response()->json($estado, 200);
    }

    public function update(UpdateEstadoRequest $request, Estado $estado)
    {
        return response()->json(
            $this->estadoService->actualizar($estado, $request->validated()),
            200
        );
    }

    public function destroy(Estado $estado)
    {
        $this->estadoService->eliminar($estado);

        return response()->json([
            'message' => 'Estado eliminado con éxito'
        ], 200);
    }
}
