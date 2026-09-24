<?php

namespace App\Services;

use App\Exceptions\AccesorioException;
use App\Models\Accesorio;
use App\Models\Renta;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccesorioService
{
    private function verificarRoles(array $roles): void
    {
        $user = Auth::user();

        if (!$user instanceof User || !$user->hasAnyRole($roles)) {
            throw new AuthorizationException(
                'No autorizado para realizar esta operación sobre accesorios.'
            );
        }
    }

    public function listar(array $filters)
    {
        $this->verificarRoles([
            'Admin_General',
            'Admin_Inventarios',
            'Gestor_Rentas',
        ]);

        $q = $filters['q'] ?? null;
        $precioMin = $filters['precio_min'] ?? null;
        $precioMax = $filters['precio_max'] ?? null;
        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';
        $perPage = $filters['per_page'] ?? 15;

        $camposPermitidos = ['id', 'nombre', 'precio_unitario', 'created_at'];

        if (!in_array($sort, $camposPermitidos)) {
            $sort = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        if ($perPage > 25) {
            $perPage = 25;
        }

        return Accesorio::query()
            ->when($q, function ($query, $q) {
                return $query->where('nombre', 'like', "$q%");
            })
            ->when($precioMin !== null, function ($query) use ($precioMin) {
                return $query->where('precio_unitario', '>=', $precioMin);
            })
            ->when($precioMax !== null, function ($query) use ($precioMax) {
                return $query->where('precio_unitario', '<=', $precioMax);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);
    }

    public function mostrar(Accesorio $accesorio)
    {
        $this->verificarRoles([
            'Admin_General',
            'Admin_Inventarios',
            'Gestor_Rentas',
        ]);

        return $accesorio;
    }

    public function crear(array $data)
    {
        $this->verificarRoles([
            'Admin_General',
            'Admin_Inventarios',
        ]);

        return Accesorio::create($data);
    }

    public function actualizar(Accesorio $accesorio, array $data)
    {
        $this->verificarRoles([
            'Admin_General',
            'Admin_Inventarios',
        ]);

        $accesorio->update($data);

        return $accesorio;
    }

    public function eliminar(Accesorio $accesorio)
    {
        $this->verificarRoles([
            'Admin_General',
            'Admin_Inventarios',
        ]);

        $tieneRentas = $accesorio->rentas()->exists();

        if ($tieneRentas) {
            throw new AccesorioException(
                'No se puede eliminar el accesorio porque tiene rentas asociadas.'
            );
        }

        $accesorio->delete();
    }

    public function agregarARenta(
        Accesorio $accesorio,
        Renta $renta,
        int $cantidad
    ) {
        $this->verificarRoles([
            'Admin_General',
            'Gestor_Rentas',
        ]);

        if ($renta->fecha_fin->isPast()) {
            throw new AccesorioException(
                'No se puede agregar un accesorio a una renta finalizada.'
            );
        }

        $yaExiste = $renta->accesorios()
            ->where('accesorios.id', $accesorio->id)
            ->exists();

        if ($yaExiste) {
            throw new AccesorioException(
                'El accesorio ya está asociado a esta renta.'
            );
        }

        return DB::transaction(function () use ($accesorio, $renta, $cantidad) {
            $precio = (float) $accesorio->precio_unitario;
            $subtotal = $precio * $cantidad;

            $renta->accesorios()->attach($accesorio->id, [
                'cantidad' => $cantidad,
                'precio_diario' => $precio,
                'subtotal' => $subtotal,
            ]);

            $renta->monto_total = (float) $renta->monto_total + $subtotal;
            $renta->save();

            return $renta->load('accesorios');
        });
    }
}
