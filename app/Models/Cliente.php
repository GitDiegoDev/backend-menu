<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    const CREATED_AT = 'fecha_alta';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre',
        'telefono',
        'sellos_actuales',
        'premios_disponibles',
        'premios_canjeados',
    ];
}
