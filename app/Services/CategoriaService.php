<?php

namespace App\Services;

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\Vehiculo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriaService
{
    /**
     * CAPA 2: Verifica que quien invoca el servicio tenga los roles correctos.
     */
    private function verificarPermisosAdministrativos(): void
    {
        $user = Auth::user();
        
        if (!$user instanceof \App\Models\User || !$user->hasAnyRole(['Admin_General', 'Admin_Inventarios'])) {
            throw new AuthorizationException('No autorizado: Se requieren privilegios de administración.');
        }
    }

    public function listarConFiltros(?string $q = null, string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $this->verificarPermisosAdministrativos();

        // Control de paginación para evitar el Error 500 con números negativos
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
        $this->verificarPermisosAdministrativos();

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
        $this->verificarPermisosAdministrativos();

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
        $this->verificarPermisosAdministrativos();

        // Regla 4: Protección del ID 1
        if ($categoria->id === 1) {
            throw new CategoriaException('No se puede eliminar la categoría principal del sistema.', 422);
        }

        // Regla 3: Vehículos asociados
        if ($categoria->vehiculos()->exists()) {
            throw new CategoriaException('No se puede eliminar la categoría porque tiene vehículos asociados.', 422);
        }

        return $categoria->delete();
    }
}