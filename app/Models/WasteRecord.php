<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'line_id',
        'shift_id',
        'product_id',
        'waste_type_id', // O 'waste_type' si decidiste guardar string directo
        'location_id',   // O 'location' si decidiste guardar string directo
        'weight_kg',
        'cause_comment',
        'user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'weight_kg' => 'decimal:2',
    ];

    // Relaciones
    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Si usas tablas para tipos y lugares, descomenta esto:
    /*
    public function wasteType(): BelongsTo
    {
        return $this->belongsTo(WasteType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    */
}