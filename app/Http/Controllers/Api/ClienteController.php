<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de clientes con filtro de búsqueda opcional.
     */
    public function index(Request $request)
    {
        $q = $request->input('q'); // Filtro de busqueda

        $clientes = Cliente::when($q, function ($query, $q) {
                return $query->where('nombre1', 'like', "%$q%")
                             ->orWhere('cedula', 'like', "%$q%")
                             ->orWhere('correo', 'like', "%$q%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return $clientes;
    }

    /**
     * Guarda un nuevo cliente en la BD.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cedula' => 'required|string|max:255|unique:clientes,cedula',
            'nombre1' => 'required|string|max:255',
            'nombre2' => 'nullable|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'anno_nacimiento' => 'required|integer|min:1900|max:' . date('Y'),
            'telefono' => 'required|string|max:255',
            'correo' => 'required|email|max:255|unique:clientes,correo',
        ]);

        $cliente = Cliente::create($validatedData);

        return $cliente;
    }

    /**
     * Muestra la info.
     */
    public function show(Cliente $cliente)
    {
        return $cliente;
    }

    /**
     * Actualiza la info.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validatedData = $request->validate([
            'cedula' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clientes', 'cedula')->ignore($cliente->id),
            ],
            'nombre1' => 'required|string|max:255',
            'nombre2' => 'nullable|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'anno_nacimiento' => 'required|integer|min:1900|max:' . date('Y'),
            'telefono' => 'required|string|max:255',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clientes', 'correo')->ignore($cliente->id),
            ],
        ]);

        $cliente->update($validatedData);

        return $cliente;
    }

    /**
     * Elimina un cliente.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}