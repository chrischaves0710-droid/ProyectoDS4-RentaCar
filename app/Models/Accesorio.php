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

    // Un accesorio puede estar presente en muchas rentas
    public function rentas()
    {
        return $this->belongsToMany(Renta::class, 'accesorio_renta', 'accesorio_id', 'renta_id')
                    ->withPivot('cantidad', 'precio_diario', 'subtotal')
                    ->withTimestamps();
    }
}