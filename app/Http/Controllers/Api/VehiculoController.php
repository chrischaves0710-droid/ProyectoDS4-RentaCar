<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Illuminate\Http\Request;


class VehiculoController extends Controller
{
    public function __construct(protected VehiculoService $vehiculoService){}

    /**
     * Listado paginado con filtros combinables, ordenamiento por 2 campos y límite máximo.
     */
    public function index(Request $request)
{
    // registros por página a un máximo de 50 (10 por defecto)
    $limit = min((int) $request->input('limit', 10), 50);

    // para que el campo a ordenar sea uno permitido; si es inválido, usa 'created_at' por defecto
    $sortBy = in_array($request->input('sort_by'), ['marca', 'anno', 'precio_diario', 'created_at']) 
        ? $request->input('sort_by') 
        : 'created_at';

    // dirección del orden: solo acepta 'asc', cualquier otra cosa será 'desc'
    $order = $request->input('order', 'desc') === 'asc' ? 'asc' : 'desc';

    $vehiculos = Vehiculo::with('categoria') // Carga la relación de categoría para evitar consultas innecesarias
        // Aplica este filtro de categoría solo si viene en la peticion
        ->when($request->input('categoria_id'), fn($q, $cat) => $q->where('categoria_id', $cat))
        
        // Aplica búsqueda solo si enviaron el parámetro ?q=texto (busca en placa o en modelo)
        ->when($request->input('q'), fn($q, $search) => 
            $q->where('placa', 'like', "{$search}%")
              ->orWhere('modelo', 'like', "%{$search}%")
        )
        
        // Primer ordenamiento dinámico según lo seleccionado por el usuario
        ->orderBy($sortBy, $order)
        
        // Segundo ordenamiento fijo por ID para desempate y garantizar consistencia en la paginación
        ->orderBy('id', 'desc')
        
        // Divide el resultado en paginas
        ->paginate($limit);

    // devuelve los resultados paginados en formato JSON con código 200 OK
    return response()->json($vehiculos);
}

    /**
     * Registro delegando en el servicio.
     */
    public function store(StoreVehiculoRequest $request)
    {
        $vehiculo = $this->vehiculoService->crear($request->validated());

        return response()->json($vehiculo, 201);
    }

    /**
     * Detalle individual con su categoria
     */
    public function show(Vehiculo $vehiculo)
    {
        return response()->json($vehiculo->load('categoria'));
    }

    /**
     * Actualización de vehículo delegando la validación de negocio al servicio.
     */
    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        $vehiculoActualizado = $this->vehiculoService->actualizar($vehiculo, $request->validated());

        return response()->json($vehiculoActualizado);
    }

    /**
     * Eliminación de vehículo delegando la verificación de dependencias al servicio.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $this->vehiculoService->eliminar($vehiculo);

        return response()->json(null, 204);
    }
}