<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionReport extends Model
{
    protected $fillable = [
        'line_id', 
        'production_date', // Antes 'fecha'
        'shift_id', 
        'coordinator_id', 
        'operator_id', 
        'status',          // Antes 'estado'
        'verified_by', 
        'verified_at'
    ];

    protected $casts = [
        'production_date' => 'date', // Antes 'fecha'
        'verified_at' => 'datetime',
    ];

    // Relaciones
    public function line(): BelongsTo { return $this->belongsTo(ProductionLine::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function coordinator(): BelongsTo { return $this->belongsTo(User::class, 'coordinator_id'); }
    public function operator(): BelongsTo { return $this->belongsTo(User::class, 'operator_id'); }
    
    public function variables(): HasMany { return $this->hasMany(ProcessVariable::class); }
}