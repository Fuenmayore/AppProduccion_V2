<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_report_id',
        'product_id',
        'brand_id',
        'recipe_id',
        'start_time',
        'end_time',
        'molds_used', // O 'mold_data' (Verifica cómo lo pusiste en tu migración)
    ];

    protected $casts = [
        'molds_used' => 'array', // Esto permite guardar el array de moldes como JSON
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}