<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoFidelizacion extends Model
{
    protected $table = 'pedidos_fidelizacion';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'pedido_uuid',
        'telefono',
    ];
}
