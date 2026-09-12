<?php

namespace App\Services;

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoriaService
{
    public function listarConFiltros(?string $q = null, string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        $allowedSorts = ['nombre', 'created_at', 'id'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        return Categoria::when($q, function ($query, $q) {
                return $query->where('nombre', 'like', "%{$q}%");
            })
            ->orderBy($sortBy, strtolower($sortDir) === 'asc' ? 'asc' : 'desc')
            ->orderBy('nombre', 'asc')
            ->paginate($perPage);
    }

    public function crear(array $data): Categoria
    {
        // Regla 1: Restricción de palabras reservadas
        if (str_contains(strtolower($data['nombre']), 'mantenimiento') || str_contains(strtolower($data['nombre']), 'inactivo')) {
            throw new CategoriaException('No se pueden registrar categorías marcadas como reservadas o inactivas.');
        }

        return DB::transaction(function () use ($data) {
            return Categoria::create($data);
        });
    }

    public function actualizar(Categoria $categoria, array $data): Categoria
    {
        // Regla 2: Bloqueo de edición por vehículos en rentas activas
        $vehiculosEnRenta = Vehiculo::where('categoria_id', $categoria->id)
            ->whereHas('rentas', function ($query) {
                $query->where('fecha_fin', '>=', now());
            })->exists();

        if ($vehiculosEnRenta) {
            throw new CategoriaException('No se puede modificar la categoría porque tiene vehículos en rentas activas.');
        }

        return DB::transaction(function () use ($categoria, $data) {
            $categoria->update($data);
            return $categoria;
        });
    }

    public function eliminar(Categoria $categoria): bool
    {
        // Regla 3: Integridad referencial con vehículos
        if ($categoria->vehiculos()->count() > 0) {
            throw new CategoriaException('No se puede eliminar la categoría porque tiene vehículos asociados.');
        }

        // Regla 4: Protección de la categoría base del sistema
        if ($categoria->id === 1) {
            throw new CategoriaException('La categoría principal del sistema está protegida y no se puede eliminar.');
        }

        return DB::transaction(function () use ($categoria) {
            return $categoria->delete();
        });
    }
}