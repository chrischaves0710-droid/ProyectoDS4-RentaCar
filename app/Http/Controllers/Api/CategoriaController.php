<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(protected CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function index(Request $request)
    {
        $q = $request->input('q');

        $categorias = $this->categoriaService->listarConFiltro($q);

        return $categorias;
    }

    public function store(StoreCategoriaRequest $request)
    {
        $validatedData = $request->validated();

        $categoria = $this->categoriaService->crear($validatedData);

        return $categoria;
    }

    public function show(Categoria $categoria)
    {
        return $categoria;
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $validatedData = $request->validated();

        $categoria = $this->categoriaService->actualizar($categoria, $validatedData);

        return $categoria;
    }

    public function destroy(Categoria $categoria)
    {
        $this->categoriaService->eliminar($categoria);

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}