<?php

namespace App\Services;

use App\Exceptions\ClienteException;
use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
        $this->validarEdadMinima($data['anno_nacimiento']);

        return Cliente::create($data);
    }

    /**
     * Obtiene un cliente.
     */
    public function obtener(Cliente $cliente): Cliente
    {
        return $cliente;
    }

    /**
     * Actualiza un cliente.
     */
    public function actualizar(Cliente $cliente, array $data): Cliente
    {
        $this->validarEdadMinima($data['anno_nacimiento']);

        $cliente->update($data);

        return $cliente;
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar(Cliente $cliente): bool
    {
        if ($cliente->rentas()->exists()) {
            throw new ClienteException(
                'No se puede eliminar el cliente porque tiene rentas asociadas.'
            );
        }

        return $cliente->delete();
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