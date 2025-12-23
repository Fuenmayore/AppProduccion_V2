<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionReport extends Model
{
    protected $fillable = [
        'line_id', 
        'production_date', 
        'shift_id', 
        'coordinator_id', 
        'operator_id', 
        'status',          
        'verified_by', 
        'verified_at'
    ];

    protected $casts = [
        'production_date' => 'date',
        'verified_at' => 'datetime',
    ];

    // Relaciones
    public function line(): BelongsTo { return $this->belongsTo(ProductionLine::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function coordinator(): BelongsTo { return $this->belongsTo(User::class, 'coordinator_id'); }
    public function operator(): BelongsTo { return $this->belongsTo(User::class, 'operator_id'); }
    
    // Variables (Horas)
    public function variables(): HasMany { return $this->hasMany(ProcessVariable::class); }

    // --- ESTA ES LA QUE FALTABA ---
    public function references(): HasMany 
    { 
        return $this->hasMany(ReferenceChange::class); 
    }
}