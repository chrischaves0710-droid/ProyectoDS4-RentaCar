<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Renta;
use Illuminate\Http\Request;

class RentaController extends Controller
{
    /**
     * Listar rentas con sus relaciones y filtros
     */
    public function index(Request $request)
    {
        // Cargar las relaciones del Modelo Eloquent
        $query = Renta::with(['cliente', 'vehiculo', 'accesorios']);

        // Filtrar por cliente_id (?cliente_id=1)
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->query('cliente_id'));
        }

        // Filtrar por vehiculo_id (?vehiculo_id=2)
        if ($request->has('vehiculo_id')) {
            $query->where('vehiculo_id', $request->query('vehiculo_id'));
        }

        $rentas = $query->get();

        return response()->json([
            'status' => 'success',
            'data'   => $rentas
        ], 200);
    }

   
    public function show($id)
    {
        $renta = Renta::with(['cliente', 'vehiculo', 'accesorios'])->find($id);

        if (!$renta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Renta no encontrada'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $renta
        ], 200);
    }
}