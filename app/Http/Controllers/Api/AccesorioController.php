<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accesorio;
use Illuminate\Http\Request;

class AccesorioController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Accesorio::query();

        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%' . $request->query('nombre') . '%');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->get()
        ], 200);
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $accesorio = Accesorio::create($request->all());

        return response()->json([
            'status'  => 'success',
            'message' => 'Accesorio creado correctamente',
            'data'    => $accesorio
        ], 201);
    }

    
    public function show($id)
    {
        $accesorio = Accesorio::find($id);

        if (!$accesorio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accesorio no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $accesorio
        ], 200);
    }

    
    public function update(Request $request, $id)
    {
        $accesorio = Accesorio::find($id);

        if (!$accesorio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accesorio no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre'          => 'sometimes|required|string|max:255',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
        ]);

        $accesorio->update($request->all());

        return response()->json([
            'status'  => 'success',
            'message' => 'Accesorio actualizado correctamente',
            'data'    => $accesorio
        ], 200);
    }

    
    public function destroy($id)
    {
        $accesorio = Accesorio::find($id);

        if (!$accesorio) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Accesorio no encontrado'
            ], 404);
        }

        $accesorio->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Accesorio eliminado correctamente'
        ], 200);
    }
}