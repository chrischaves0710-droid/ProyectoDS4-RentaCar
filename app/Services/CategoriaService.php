<?php

namespace App\Services;

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        // REGLA DE NEGOCIO 1: Longitud mínima del nombre
        if (strlen(trim($data['nombre'])) < 3) {
            throw new CategoriaException('El nombre de la categoría debe contener al menos 3 caracteres.');
        }

        // REGLA DE NEGOCIO 2: Bloqueo de palabras no permitidas
        if (str_contains(strtolower($data['nombre']), 'prohibido')) {
            throw new CategoriaException('El nombre de la categoría contiene términos no permitidos.');
        }

        return DB::transaction(function () use ($data) {
            return Categoria::create($data);
        });
    }

    public function actualizar(Categoria $categoria, array $data): Categoria
    {
        // REGLA DE NEGOCIO 3: Protección de la categoría principal del sistema
        if ($categoria->id === 1) {
            throw new CategoriaException('La categoría base del sistema no puede ser modificada.');
        }

        return DB::transaction(function () use ($categoria, $data) {
            $categoria->update($data);
            return $categoria;
        });
    }

    public function eliminar(Categoria $categoria): bool
    {
        // REGLA DE NEGOCIO 4: Integridad referencial (Categorias -> Vehiculos)
        if (method_exists($categoria, 'vehiculos') && $categoria->vehiculos()->count() > 0) {
            throw new CategoriaException('No se puede eliminar la categoría porque existen vehículos asociados a ella.');
        }

        return DB::transaction(function () use ($categoria) {
            return $categoria->delete();
        });
    }
}