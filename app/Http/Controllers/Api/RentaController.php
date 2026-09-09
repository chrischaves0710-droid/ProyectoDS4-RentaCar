<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Renta;
use Illuminate\Http\Request;

class RentaController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Renta::with(['cliente', 'vehiculo', 'accesorios']);

        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->query('cliente_id'));
        }

        if ($request->has('vehiculo_id')) {
            $query->where('vehiculo_id', $request->query('vehiculo_id'));
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->get()
        ], 200);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'vehiculo_id'   => 'required|exists:vehiculos,id',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
            'precio_diario' => 'required|numeric|min:0',
            'monto_total'   => 'required|numeric|min:0',
        ]);

        $renta = Renta::create($request->all());

        return response()->json([
            'status'  => 'success',
            'message' => 'Renta registrada correctamente',
            'data'    => $renta->load(['cliente', 'vehiculo', 'accesorios'])
        ], 201);
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

    
    public function update(Request $request, $id)
    {
        $renta = Renta::find($id);

        if (!$renta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Renta no encontrada'
            ], 404);
        }

        $request->validate([
            'cliente_id'    => 'sometimes|required|exists:clientes,id',
            'vehiculo_id'   => 'sometimes|required|exists:vehiculos,id',
            'fecha_inicio'  => 'sometimes|required|date',
            'fecha_fin'     => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'precio_diario' => 'sometimes|required|numeric|min:0',
            'monto_total'   => 'sometimes|required|numeric|min:0',
        ]);

        $renta->update($request->all());

        return response()->json([
            'status'  => 'success',
            'message' => 'Renta actualizada correctamente',
            'data'    => $renta->load(['cliente', 'vehiculo', 'accesorios'])
        ], 200);
    }

    
    public function destroy($id)
    {
        $renta = Renta::find($id);

        if (!$renta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Renta no encontrada'
            ], 404);
        }

        $renta->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Renta eliminada correctamente'
        ], 200);
    }
}