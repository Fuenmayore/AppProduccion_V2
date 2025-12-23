<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','brand_id', 'sku', 'allowed_line_ids', 'default_mold'];

    protected $casts = [
        'allowed_line_ids' => 'array', // <--- ESTO ES VITAL
    ];
}