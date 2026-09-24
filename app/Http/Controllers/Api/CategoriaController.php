<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(protected CategoriaService $categoriaService)
    {
    }

    /**
     * GET /api/categorias
     * Paginación y filtro por nombre (200 OK)
     */
    public function index(Request $request)
    {
        // se extraen los parámetros de la petición HTTP
        $q = $request->input('q');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = $request->input('per_page', 10);

        // Se delega la consulta al servicio, que maneja la lógica de negocio y permisos
        $data = $this->categoriaService->listarConFiltros($q, $sortBy, $sortDir, $perPage);

        return CategoriaResource::collection($data);
    }

    /**
     * POST /api/categorias
     * Retorna 201 Created con encabezado Location
     */
    public function store(StoreCategoriaRequest $request)
    {
        $validatedData = $request->validated();

        $categoria = $this->categoriaService->crear($validatedData);

        return (new CategoriaResource($categoria))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('categorias.show', ['categoria' => $categoria->id]));
    }

    /**
     * GET /api/categorias/{categoria}
     * Muestra el detalle (200 OK)
     */
    public function show(Categoria $categoria)
    {
        return new CategoriaResource($categoria->load('vehiculos'));
    }

    /**
     * PUT/PATCH /api/categorias/{categoria}
     * Actualiza el recurso (200 OK)
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $validatedData = $request->validated();

        $categoriaActualizada = $this->categoriaService->actualizar($categoria, $validatedData);

        return new CategoriaResource($categoriaActualizada);
    }

    /**
     * DELETE /api/categorias/{categoria}
     * Elimina el recurso (204 No Content)
     */
    public function destroy(Categoria $categoria)
    {
        $this->categoriaService->eliminar($categoria);

        return response()->json(null, 204);
    }

    /**
     * GET /api/categorias/{categoria}/vehiculos
     * Recurso anidado con paginación
     */
    public function vehiculos(Categoria $categoria)
    {
        return $categoria->vehiculos()->paginate(10);
    }
}