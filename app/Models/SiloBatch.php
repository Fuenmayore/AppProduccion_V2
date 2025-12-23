<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiloBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'silo_name',
        'internal_batch_code',
        'raw_material_id',
        'loading_date',
        'operator_id',
        'quality_check',
        'is_active',
        // --- CAMPOS NUEVOS AGREGADOS ---
        'initial_quantity',
        'current_quantity',
    ];

    protected $casts = [
        'loading_date' => 'date',
        'quality_check' => 'array',
        'is_active' => 'boolean',
        'initial_quantity' => 'decimal:2',
        'current_quantity' => 'decimal:2',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

/*     public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class); // Si usas este modelo
    } */
}