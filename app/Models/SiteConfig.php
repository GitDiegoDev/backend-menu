<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteConfig extends Model
{
    protected $fillable = ['open_time', 'close_time', 'open_days', 'closed_dates'];

    protected $casts = [
        'open_days' => 'array',
        'closed_dates' => 'array',
    ];
}