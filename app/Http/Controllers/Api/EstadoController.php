<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    // Listar todos los estados
    public function index()
    {
        return response()->json(Estado::all(), 200);
    }

    // Crear un nuevo estado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:estados,nombre',
        ]);

        $estado = Estado::create($validated);

        return response()->json([
            'message' => 'Estado creado con éxito',
            'data' => $estado
        ], 201);
    }

    // Mostrar un estado específico
    public function show($id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        return response()->json($estado, 200);
    }

    // Actualizar un estado
    public function update(Request $request, $id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:estados,nombre,' . $id,
        ]);

        $estado->update($validated);

        return response()->json([
            'message' => 'Estado actualizado con éxito',
            'data' => $estado
        ], 200);
    }

    // Eliminar un estado
    public function destroy($id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json(['message' => 'Estado no encontrado'], 404);
        }

        $estado->delete();

        return response()->json(['message' => 'Estado eliminado con éxito'], 200);
    }
}
