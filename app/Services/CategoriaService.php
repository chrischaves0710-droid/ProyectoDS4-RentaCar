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
        $nombreLimpio = trim($data['nombre']);

        // Regla 1: Evitar categorías duplicadas (insensible a mayúsculas/minúsculas)
        $existe = Categoria::whereRaw('LOWER(nombre) = ?', [strtolower($nombreLimpio)])->exists();

        if ($existe) {
            throw new CategoriaException('Ya existe una categoría registrada con ese nombre.');
        }

        $data['nombre'] = $nombreLimpio;

        return Categoria::create($data);
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
        // EVALUAR REGLA 4: Protección del ID 1
        if ($categoria->id === 1) {
            throw new CategoriaException('No se puede eliminar la categoría principal del sistema.', 422);
        }

        // EVALUAR REGLA 3: Vehículos asociados
        if ($categoria->vehiculos()->exists()) {
            throw new CategoriaException('No se puede eliminar la categoría porque tiene vehículos asociados.', 422);
        }

        return $categoria->delete();
    }
}