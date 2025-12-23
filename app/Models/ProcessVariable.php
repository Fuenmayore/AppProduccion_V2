<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_report_id', 
        'recorded_at', 
        'silo_batch_id', 
        'data', 
        'observacion', 
        'img_limpieza'
    ];

    protected $casts = [
        'data' => 'array',
        // 'recorded_at' => 'datetime', // Mantener comentado para evitar conflictos con JS
    ];

    // Relación con el Reporte Padre
    public function report(): BelongsTo
    {
        return $this->belongsTo(ProductionReport::class, 'production_report_id');
    }

    // --- ESTA ES LA RELACIÓN QUE FALTABA ---
    public function siloBatch(): BelongsTo
    {
        return $this->belongsTo(SiloBatch::class, 'silo_batch_id');
    }
}