<?php

namespace App\Services;

use App\Exceptions\ClienteException;
use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClienteService
{
    /**
     * Lista clientes con búsqueda, filtros, ordenamiento y paginación.
     */
    public function listar(
        ?string $q = null,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {

        $this->verificarLectura();

        $perPage = min(max($perPage, 1), 50);

        $allowedSorts = [
            'id',
            'nombre1',
            'apellido1',
            'anno_nacimiento',
            'created_at'
        ];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        return Cliente::when($q, function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nombre1', 'like', "%{$q}%")
                          ->orWhere('nombre2', 'like', "%{$q}%")
                          ->orWhere('apellido1', 'like', "%{$q}%")
                          ->orWhere('apellido2', 'like', "%{$q}%")
                          ->orWhere('cedula', 'like', "%{$q}%")
                          ->orWhere('correo', 'like', "%{$q}%");
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'asc')
            ->paginate($perPage);
    }

    /**
     * Crea un nuevo cliente.
     */
    public function crear(array $data): Cliente
    {
        $this->verificarAdministracion();

        $this->validarEdadMinima($data['anno_nacimiento']);

        return Cliente::create($data);
    }

    /**
     * Obtiene un cliente.
     */
    public function obtener(Cliente $cliente): Cliente
    {
        $this->verificarAccesoCliente($cliente);

        return $cliente;
    }

    /**
     * Actualiza un cliente.
     */
    public function actualizar(Cliente $cliente, array $data): Cliente
    {
        $this->verificarAdministracion();

        $this->validarEdadMinima($data['anno_nacimiento']);

        $cliente->update($data);

        return $cliente;
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar(Cliente $cliente): bool
    {
        $this->verificarAdministracion();

        if ($cliente->rentas()->exists()) {
            throw new ClienteException(
                'No se puede eliminar el cliente porque tiene rentas asociadas.'
            );
        }

        return $cliente->delete();
    }

    private function usuarioAutenticado(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            throw new AuthorizationException('Usuario no autorizado.');
        }

        return $user;
    }

    private function verificarLectura(): void
    {
        $user = $this->usuarioAutenticado();

        if (!$user->hasAnyRole(['Admin_General', 'Gestor_Rentas'])) {
            throw new AuthorizationException(
                'No autorizado para consultar clientes.'
            );
        }
    }

    private function verificarAdministracion(): void
    {
        $user = $this->usuarioAutenticado();

        if (!$user->hasRole('Admin_General')) {
            throw new AuthorizationException(
                'No autorizado para administrar clientes.'
            );
        }
    }

    private function verificarAccesoCliente(Cliente $cliente): void
    {
        $user = $this->usuarioAutenticado();

        if ($user->hasAnyRole(['Admin_General', 'Gestor_Rentas'])) {
            return;
        }

        if ($user->hasRole('Cliente')) {
            Gate::forUser($user)->authorize('view', $cliente);
            return;
        }

        throw new AuthorizationException(
            'No autorizado para consultar este cliente.'
        );
    }

    /**
     * Regla de negocio// el cliente debe ser mayor de edad.
     */
    private function validarEdadMinima(int $annoNacimiento): void
    {
        $edad = date('Y') - $annoNacimiento;

        if ($edad < 18) {
            throw new ClienteException(
                'El cliente debe ser mayor de edad.'
            );
        }
    }
}