<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoriaService
{
    public function listarConFiltro(?string $busqueda = null, int $perPage = 25): LengthAwarePaginator
    {
        return Categoria::when($busqueda, function ($query, $q) {
                return $query->where('nombre', 'like', "%{$q}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function crear(array $data): Categoria
    {
        return Categoria::create($data);
    }

    public function actualizar(Categoria $categoria, array $data): Categoria
    {
        $categoria->update($data);
        return $categoria;
    }

    public function eliminar(Categoria $categoria): bool
    {
        return $categoria->delete();
    }
}