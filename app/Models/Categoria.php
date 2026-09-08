<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;
    /**
     * La tabla asociada al modelo.
    */
  
    protected $table = 'categorias';
    /**
     * Los atributos que son asignables de forma masiva.
    */
   
    protected $fillable = [
        'nombre',
    ];

    /**
     * Obtiene todos los vehículos pertenecientes a esta categoría.
     */
    public function vehiculos(): HasMany
    {
        return $table = $this->hasMany(Vehiculo::class, 'categoria_id');
    }
}