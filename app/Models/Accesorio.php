<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesorio extends Model
{
    use HasFactory;

    protected $table = 'accesorios';

    protected $fillable = [
        'nombre',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Definición de la relación con el modelo Renta
    public function rentas()
    {
        return $this->belongsToMany(Renta::class, 'accesorio_renta', 'accesorio_id', 'renta_id')
                    ->withPivot('cantidad', 'precio_diario', 'subtotal')
                    ->withTimestamps();
    }
}