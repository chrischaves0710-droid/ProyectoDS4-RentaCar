<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculosController extends Controller
{
    /**
     * Muestra una lista paginada de vehículos con filtros opcionales.
     */
    public function index(Request $request)
    {
        // Captura los parámetros de búsqueda de la petición
        $estadoId = $request->input('estado_id');
        $categoriaId = $request->input('categoria_id');
        $q = $request->input('q'); // Filtro por placa, marca o modelo

        $vehiculos = Vehiculo::with(['categoria', 'estado'])
            ->when($estadoId, function ($query, $estadoId) {
                return $query->where('estado_id', $estadoId);
            })
            ->when($categoriaId, function ($query, $categoriaId) {
                return $query->where('categoria_id', $categoriaId);
            })
            ->when($q, function ($query, $q) {
                return $query->where('placa', 'like', "$q%")
                    ->orWhere('marca', 'like', "%$q%")
                    ->orWhere('modelo', 'like', "%$q%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return $vehiculos;
    }

    /**
     * Guarda un nuevo vehículo en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar campos de la tabla
        $validatedData = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'anno' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kilometraje' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'estado_id' => 'required|exists:estados,id',
        ]);

        // se crea el registro con los datos validados
        $vehiculo = Vehiculo::create($validatedData);

        return $vehiculo;
    }

    /**
     * Muestra la información de un vehículo con sus relaciones.
     */
    public function show(Vehiculo $vehiculo)
    {
        // Cargar las relaciones de categoría y estado
        return $vehiculo->load(['categoria', 'estado']);
    }

    /**
     * Actualiza la información de un vehículo existente.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        // Validar ignorando la placa del vehículo actual
        $validatedData = $request->validate([
            'placa' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehiculos', 'placa')->ignore($vehiculo->id)
            ],
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'anno' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kilometraje' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $vehiculo->update($validatedData);

        return $vehiculo;
    }

    /**
     * Elimina un vehiculo.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return response()->json(['message' => 'Vehículo eliminado correctamente']);
    }
}