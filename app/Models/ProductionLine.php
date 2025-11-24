<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLine extends Model
{
    protected $fillable = ['name', 'slug', 'config'];

    protected $casts = [
        'config' => 'array', // ¡Clave! Convierte el JSON a Array automáticamente
    ];
}