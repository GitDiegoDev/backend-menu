<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'description',
    'price',
    'active',
    'day_of_week',
];
    protected $casts = [
    'active' => 'boolean',
    'price' => 'float',
];
}
