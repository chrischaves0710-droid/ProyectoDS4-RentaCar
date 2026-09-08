<?php

namespace App\Models;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'vehiculos';

    // Campos permitidos para guardar
    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anno',
        'kilometraje',
        'categoria_id',
        'estado_id',
    ];

    // Convertir tipos de datos a numeros enteros
    protected $casts = [
        'anno' => 'integer',
        'kilometraje' => 'integer',
    ];

    // un carro pertenece a una categoría
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // un vehículo pertenece a un estado
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    // un vehículo puede tener muchas rentas
    public function rentas(): HasMany
    {
        return $this->hasMany(Renta::class);
    }
}