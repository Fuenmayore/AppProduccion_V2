<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionReport;
use App\Models\SiloBatch;
use App\Models\WasteRecord; // Asegúrate de importar este modelo
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // 1. ESTADO DE LÍNEAS + EFICIENCIA
        $linesStatus = ProductionLine::all()->map(function ($line) use ($now, $today) {
            
            $activeReport = ProductionReport::where('line_id', $line->id)
                ->whereDate('production_date', $today)
                ->where('status', 'Running')
                ->with(['shift', 'coordinator', 'operator'])
                ->latest()
                ->first();

            $lastVariable = $activeReport 
                ? $activeReport->variables()->latest('recorded_at')->first() 
                : null;
            
            // Variables por defecto
            $statusColor = 'gray';
            $statusText = 'DETENIDA';
            $minutesSinceLast = 0;
            $timeSinceHuman = '--:--';
            $efficiency = 0; // DATO DE VALOR: Eficiencia del turno

            if ($activeReport) {
                $statusColor = 'green';
                $statusText = 'OPERANDO';

                // A. Cálculo de Tiempo (Supervisión)
                if ($lastVariable) {
                    $lastRecordTime = Carbon::parse($today->toDateString() . ' ' . $lastVariable->recorded_at);
                    $minutesSinceLast = (int) $lastRecordTime->diffInMinutes($now);

                    if ($minutesSinceLast < 60) {
                        $timeSinceHuman = "Hace {$minutesSinceLast} min";
                    } else {
                        $hours = round($minutesSinceLast / 60, 1);
                        $timeSinceHuman = "Hace {$hours} " . ($hours == 1 ? 'hora' : 'horas');
                    }

                    // Semáforo de Alerta (Prevención)
                    if ($minutesSinceLast > 75) {
                        $statusColor = 'yellow';
                        $statusText = 'ATRASADA';
                    }
                    if ($minutesSinceLast > 120) {
                        $statusColor = 'red';
                        $statusText = 'CRÍTICO';
                    }
                } else {
                    $statusColor = 'blue'; 
                    $statusText = 'INICIANDO';
                    $timeSinceHuman = 'Esperando datos...';
                }

                // B. Cálculo de Eficiencia (Medición)
                // Asumimos meta teórica: 1 registro por hora desde el inicio del turno
                if ($activeReport->shift) {
                    // Hora inicio turno (ej: 06:00)
                    $startShift = Carbon::parse($today->toDateString() . ' ' . $activeReport->shift->start_time);
                    // Si es ahora (ej: 09:00), han pasado 3 horas -> Debería haber 3 registros
                    $hoursElapsed = $startShift->diffInHours($now);
                    
                    // Evitar división por cero y horas negativas
                    if ($hoursElapsed >= 1) {
                        $realRecords = $activeReport->variables()->count();
                        $efficiency = min(round(($realRecords / $hoursElapsed) * 100), 100);
                    } else {
                        $efficiency = 100; // Primera hora
                    }
                }
            }

            return [
                'id' => $line->id,
                'name' => $line->name,
                'slug' => $line->slug,
                'is_active' => (bool) $activeReport,
                'shift' => $activeReport ? $activeReport->shift->name : '-',
                'coordinator' => $activeReport ? $activeReport->coordinator->name : '-',
                'operator' => $activeReport ? $activeReport->operator->name : '-',
                'last_record' => $lastVariable ? Carbon::parse($lastVariable->recorded_at)->format('g:i A') : '--:--',
                'minutes_since' => $minutesSinceLast,
                'time_since' => $timeSinceHuman,
                'records_count' => $activeReport ? $activeReport->variables()->count() : 0,
                'efficiency' => $efficiency, // <--- NUEVO
                'status_color' => $statusColor,
                'status_text' => $statusText,
            ];
        });

        // 2. CALIDAD Y DESPERDICIOS (NUEVO - MEDICIÓN)
        $wasteQuery = WasteRecord::whereDate('date', $today);
        $totalWaste = $wasteQuery->sum('weight_kg');
        
        // Top 3 Causas de Pérdida (Pareto)
        $topDefects = WasteRecord::whereDate('date', $today)
            ->select('cause_comment', DB::raw('SUM(weight_kg) as total'))
            ->groupBy('cause_comment')
            ->orderByDesc('total')
            ->take(3)
            ->get()
            ->map(function($item){
                return [
                    'name' => \Illuminate\Support\Str::limit($item->cause_comment, 20), // Acortar nombre
                    'total' => $item->total
                ];
            });

        // 3. ESTADÍSTICAS GLOBALES
        $stats = [
            'active_lines' => $linesStatus->where('is_active', true)->count(),
            'total_records' => ProductionReport::whereDate('production_date', $today)
                ->withCount('variables')
                ->get()
                ->sum('variables_count'),
            'total_waste' => number_format($totalWaste, 1, ',', '.'), // <--- NUEVO KPI
            'date_human' => $now->locale('es')->isoFormat('dddd, D [de] MMMM YYYY'),
            'julian_day' => $now->dayOfYear,
            'week_number' => $now->weekOfYear,
            'current_time' => $now->format('g:i A'),
        ];

        // 4. ESTADO DE SILOS (Mantenemos la lógica visual)
        $silos = SiloBatch::where('is_active', true)->get()->map(function ($silo) {
            $percentage = 0;
            if ($silo->initial_quantity > 0) {
                $percentage = ($silo->current_quantity / $silo->initial_quantity) * 100;
            }
            $color = 'green';
            if ($percentage < 50) $color = 'yellow';
            if ($percentage < 20) $color = 'red';

            return [
                'id' => $silo->id,
                'name' => $silo->silo_name,
                'code' => $silo->internal_batch_code,
                'material' => $silo->quality_check['material_type'] ?? 'Materia Prima',
                'current' => number_format($silo->current_quantity, 0, ',', '.'),
                'initial' => number_format($silo->initial_quantity, 0, ',', '.'),
                'percentage' => round($percentage),
                'color' => $color,
            ];
        });

        return Inertia::render('Dashboard', [
            'linesStatus' => $linesStatus,
            'stats' => $stats,
            'silos' => $silos,
            'topDefects' => $topDefects, // <--- NUEVO
        ]);
    }
}