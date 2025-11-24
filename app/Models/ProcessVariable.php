<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessVariable extends Model
{
    protected $fillable = [
        'production_report_id', 'recorded_at', 
        'silo_batch_id', 'data', 'observacion', 'img_limpieza'
    ];

    protected $casts = [
        // ESTA LÍNEA ES VITAL: 
        // Permite guardar ['trabatto' => 12] y que Laravel lo guarde como JSON
        // y al leerlo te lo devuelva como array.
        'data' => 'array', 
        'recorded_at' => 'datetime', // O 'string' si solo guardas 'H:i:s'
    ];

    public function report() { return $this->belongsTo(ProductionReport::class, 'production_report_id'); }
}