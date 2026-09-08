<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'cedula',
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'anno_nacimiento',
        'telefono',
        'correo',
    ];

    public function rentas(): HasMany
    {
        return $this->hasMany(Renta::class);//un cliente puede tener muchas rentas
    }
}