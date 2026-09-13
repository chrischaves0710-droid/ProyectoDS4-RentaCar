<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaVehiculo extends Model
{
    protected $table = 'auditoria_vehiculos';

    protected $fillable = [
        'vehiculo_id',
        'accion',
    ];
}