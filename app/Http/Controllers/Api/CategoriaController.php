<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(
        protected CategoriaService $categoriaService
    ) {}

    public function index(Request $request)
    {
        return $this->categoriaService->listarConFiltro($request->input('q'));
    }

    public function store(StoreCategoriaRequest $request): Categoria
    {
        return $this->categoriaService->crear($request->validated());
    }

    public function show(Categoria $categoria): Categoria
    {
        return $categoria;
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): Categoria
    {
        return $this->categoriaService->actualizar($categoria, $request->validated());
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        $this->categoriaService->eliminar($categoria);
        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}