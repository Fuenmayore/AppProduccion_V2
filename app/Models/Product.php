<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importante

class Product extends Model
{
    protected $fillable = ['name','brand_id', 'sku', 'allowed_line_ids', 'default_mold'];

    protected $casts = [
        'allowed_line_ids' => 'array', // <--- ESTO ES VITAL
    ];
    /**
    * Relación: El producto pertenece a una marca
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}