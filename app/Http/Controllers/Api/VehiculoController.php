<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Http\Resources\VehiculoResource; 
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function __construct(protected VehiculoService $vehiculoService){}

    public function index(Request $request)
    {
        // se agrupan los filtros para pasarlos limpiamente al servicio
        $filtros = [
            'categoria_id' => $request->input('categoria_id'),
            'q' => $request->input('q')
        ];
        
        $sortBy = $request->input('sort_by', 'created_at');
        $order = $request->input('order', 'desc');
        $limit = (int) $request->input('limit', 10);

        // se delega la consulta a la Capa 2
        $vehiculos = $this->vehiculoService->listarConFiltros($filtros, $sortBy, $order, $limit);

        return VehiculoResource::collection($vehiculos);
    }

    public function store(StoreVehiculoRequest $request)
    {
        $vehiculo = $this->vehiculoService->crear($request->validated());

        return (new VehiculoResource($vehiculo))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url('/api/vehiculos/' . $vehiculo->id));
    }

    public function show(Vehiculo $vehiculo)
    {
        // carga y verificación de lectura a la Capa 2
        $vehiculoCargado = $this->vehiculoService->mostrar($vehiculo);

        return new VehiculoResource($vehiculoCargado);
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        $vehiculoActualizado = $this->vehiculoService->actualizar($vehiculo, $request->validated());

        return new VehiculoResource($vehiculoActualizado);
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $this->vehiculoService->eliminar($vehiculo);

        return response()->noContent();
    }
}