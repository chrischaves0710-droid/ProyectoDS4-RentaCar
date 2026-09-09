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

        $accesorios = $query->get();

        return response()->json([
            'status' => 'success',
            'data'   => $accesorios
        ], 200);
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
}