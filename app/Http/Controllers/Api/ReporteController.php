<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Renta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // consulta con Scopes reutilizables de Eloquent
    public function obtenerRentasVigentesPorMonto(Request $request)
    {
        $montoMinimo = $request->input('monto', 50.00);

        $rentas = Renta::conFechaFinVigente()
            ->conMontoMinimo($montoMinimo)
            ->with(['cliente', 'vehiculo'])
            ->get();

        return response()->json([
            'consulta' => 'Scopes de Eloquent',
            'monto_minimo' => $montoMinimo,
            'total_registros' => $rentas->count(),
            'data' => $rentas
        ]);
    }

    // consulta de agregación con GROUP BY por vehículo
    public function obtenerIngresosYTotalesPorVehiculo()
    {
        $reporte = Renta::select(
                'vehiculo_id',
                DB::raw('COUNT(id) as total_rentas'),
                DB::raw('SUM(monto_total) as ingresos_totales'),
                DB::raw('ROUND(AVG(monto_total), 2) as promedio_renta')
            )
            ->groupBy('vehiculo_id')
            ->with('vehiculo:id,placa,marca,modelo')
            ->orderByDesc('ingresos_totales')
            ->get();

        return response()->json([
            'consulta' => 'Agregación con GROUP BY',
            'data' => $reporte
        ]);
    }

    //consulta sobre la tabla pivote accesorio_renta
    public function obtenerEstadisticasAccesoriosRenta()
    {
        $reporte = DB::table('accesorio_renta')
            ->join('accesorios', 'accesorio_renta.accesorio_id', '=', 'accesorios.id')
            ->select(
                'accesorios.id as accesorio_id',
                'accesorios.nombre',
                DB::raw('COUNT(accesorio_renta.renta_id) as veces_alquilado'),
                DB::raw('SUM(accesorio_renta.cantidad) as total_unidades'),
                DB::raw('SUM(accesorio_renta.subtotal) as total_recaudado')
            )
            ->groupBy('accesorios.id', 'accesorios.nombre')
            ->orderByDesc('total_unidades')
            ->get();

        return response()->json([
            'consulta' => 'Tabla intermedia (accesorio_renta)',
            'data' => $reporte
        ]);
    }
}