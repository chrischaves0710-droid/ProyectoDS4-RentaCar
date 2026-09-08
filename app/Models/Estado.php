<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    protected $hidden = ['created_at', 'updated_at'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }
}
