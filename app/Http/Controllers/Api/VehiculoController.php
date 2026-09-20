<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Http\Resources\VehiculoResource; // importar recurso
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function __construct(protected VehiculoService $vehiculoService){}

    public function index(Request $request)
    {
        // limite maximo
        $limit = min((int) $request->input('limit', 10), 50);

        // campo a ordenar
        $sortBy = in_array($request->input('sort_by'), ['marca', 'anno', 'precio_diario', 'created_at']) 
            ? $request->input('sort_by') 
            : 'created_at';

        // direccion asc o desc
        $order = $request->input('order', 'desc') === 'asc' ? 'asc' : 'desc';

        // consulta con filtros y relaciones
        $vehiculos = Vehiculo::with(['categoria', 'estado']) 
            ->when($request->input('categoria_id'), fn($q, $cat) => $q->where('categoria_id', $cat))
            ->when($request->input('q'), fn($q, $search) => 
                $q->where('placa', 'like', "{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
            )
            ->orderBy($sortBy, $order)
            ->orderBy('id', 'desc')
            ->paginate($limit);

        // devolver con paginacion
        return VehiculoResource::collection($vehiculos);
    }

    public function store(StoreVehiculoRequest $request)
    {
        $vehiculo = $this->vehiculoService->crear($request->validated());

        // 201 con header location
        return (new VehiculoResource($vehiculo))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url('/api/vehiculos/' . $vehiculo->id));
    }

    public function show(Vehiculo $vehiculo)
    {
        // cargar relaciones
        $vehiculo->load(['categoria', 'estado']);

        // devolver recurso
        return new VehiculoResource($vehiculo);
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        $vehiculoActualizado = $this->vehiculoService->actualizar($vehiculo, $request->validated());

        // cargar relaciones nuevas
        $vehiculoActualizado->load(['categoria', 'estado']);

        // devolver recurso
        return new VehiculoResource($vehiculoActualizado);
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $this->vehiculoService->eliminar($vehiculo);

        // 204 sin contenido
        return response()->noContent();
    }
}