<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renta extends Model
{
    use HasFactory;

    protected $table = 'rentas';

    protected $fillable = [
        'cliente_id',
        'vehiculo_id',
        'fecha_inicio',
        'fecha_fin',
        'precio_diario',
        'monto_total',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'precio_diario' => 'decimal:2',
        'monto_total' => 'decimal:2',
    ];

   

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    

    public function accesorios()
    {
        return $this->belongsToMany(Accesorio::class, 'accesorio_renta', 'renta_id', 'accesorio_id')
                    ->withPivot('cantidad', 'precio_diario', 'subtotal')
                    ->withTimestamps();
    }
}