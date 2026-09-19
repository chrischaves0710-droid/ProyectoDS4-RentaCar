<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Http\Resources\VehiculoResource; // importacion de recurso
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function __construct(protected VehiculoService $vehiculoService){}

    public function index(Request $request)
    {
        // limite de registro
        $limit = min((int) $request->input('limit', 10), 50);

        // validacion de campo para ordenamiento
        $sortBy = in_array($request->input('sort_by'), ['marca', 'anno', 'precio_diario', 'created_at']) 
            ? $request->input('sort_by') 
            : 'created_at';

        // direccion de ordenamiento
        $order = $request->input('order', 'desc') === 'asc' ? 'asc' : 'desc';

        // carga de relacion de categoria y estado para el resource
        $vehiculos = Vehiculo::with(['categoria', 'estado']) 
            ->when($request->input('categoria_id'), fn($q, $cat) => $q->where('categoria_id', $cat))
            ->when($request->input('q'), fn($q, $search) => 
                $q->where('placa', 'like', "{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
            )
            ->orderBy($sortBy, $order)
            ->orderBy('id', 'desc')
            ->paginate($limit);

        // respuesta formateada con paginacion
        return VehiculoResource::collection($vehiculos);
    }

    public function store(StoreVehiculoRequest $request)
    {
        $vehiculo = $this->vehiculoService->crear($request->validated());

        // respuesta 201 con formato y encabezado location segun requerimiento
        return (new VehiculoResource($vehiculo))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url('/api/vehiculos/' . $vehiculo->id));
    }

    public function show(Vehiculo $vehiculo)
    {
        // carga de relacion
        $vehiculo->load(['categoria', 'estado']);

        // respuesta formateada
        return new VehiculoResource($vehiculo);
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        $vehiculoActualizado = $this->vehiculoService->actualizar($vehiculo, $request->validated());

        // carga de relacion para asegurar estructura completa
        $vehiculoActualizado->load(['categoria', 'estado']);

        // respuesta formateada
        return new VehiculoResource($vehiculoActualizado);
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $this->vehiculoService->eliminar($vehiculo);

        // respuesta 204 sin contenido
        return response()->noContent();
    }
}