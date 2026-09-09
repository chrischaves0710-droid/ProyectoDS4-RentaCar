<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    /**
     * Muestra una lista paginada de categorías con filtro de búsqueda opcional.
     */
    public function index(Request $request)
    {
        $q = $request->input('q'); // Filtro por nombre de la categoría

        $categorias = Categoria::when($q, function ($query, $q) {
                return $query->where('nombre', 'like', "%$q%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return $categorias;
    }

    /**
     * Guarda una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ]);

        $categoria = Categoria::create($validatedData);

        return $categoria;
    }

    /**
     * Muestra la información de una categoría específica.
     */
    public function show(Categoria $categoria)
    {
        return $categoria;
    }

    /**
     * Actualiza la información de una categoría existente.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')->ignore($categoria->id),
            ],
        ]);

        $categoria->update($validatedData);

        return $categoria;
    }

    /**
     * Elimina una categoría.
     */
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}