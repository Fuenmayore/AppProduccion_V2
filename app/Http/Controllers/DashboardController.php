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

        // 1. ESTADO DE LÍNEAS + PRODUCTO ACTIVO + EFICIENCIA
        $linesStatus = ProductionLine::all()->map(function ($line) use ($now, $today) {
            
            // Buscamos el reporte activo con sus relaciones de Producto y Marca
            $activeReport = ProductionReport::where('line_id', $line->id)
                ->whereDate('production_date', $today)
                ->where('status', 'Running')
                // Eager loading de referencias para saber qué se está produciendo
                ->with(['shift', 'coordinator', 'operator', 'references.product', 'references.brand'])
                ->latest()
                ->first();

            $lastVariable = $activeReport 
                ? $activeReport->variables()->latest('recorded_at')->first() 
                : null;
            
            // Referencia actual (el último cambio de producto sin hora de fin)
            $currentReference = $activeReport 
                ? $activeReport->references->whereNull('end_time')->first() 
                : null;
            
            $statusColor = 'gray';
            $statusText = 'DETENIDA';
            $minutesSinceLast = 0;
            $timeSinceHuman = '--:--';
            $efficiency = 0;

            if ($activeReport) {
                $statusColor = 'green';
                $statusText = 'OPERANDO';

                if ($lastVariable) {
                    $lastRecordTime = Carbon::parse($today->toDateString() . ' ' . $lastVariable->recorded_at);
                    $minutesSinceLast = (int) $lastRecordTime->diffInMinutes($now);
                    $timeSinceHuman = $minutesSinceLast < 60 ? "Hace {$minutesSinceLast} min" : "Hace " . round($minutesSinceLast / 60, 1) . " h";

                    // Semáforo de Alerta por falta de registros
                    if ($minutesSinceLast > 75) { $statusColor = 'yellow'; $statusText = 'ATRASADA'; }
                    if ($minutesSinceLast > 120) { $statusColor = 'red'; $statusText = 'CRÍTICO'; }
                } else {
                    $statusColor = 'blue'; 
                    $statusText = 'INICIANDO';
                    $timeSinceHuman = 'Esperando datos...';
                }

                // Cálculo de Eficiencia (Registros reales vs Esperados por hora)
                if ($activeReport->shift) {
                    $startShift = Carbon::parse($today->toDateString() . ' ' . $activeReport->shift->start_time);
                    $hoursElapsed = max(1, $startShift->diffInHours($now));
                    $realRecords = $activeReport->variables()->count();
                    $efficiency = min(round(($realRecords / $hoursElapsed) * 100), 100);
                }
            }

            return [
                'id' => $line->id,
                'name' => $line->name,
                'slug' => $line->slug,
                'is_active' => (bool) $activeReport,
                'product' => $currentReference ? $currentReference->product->name : '-', // <--- INFO PARA PASTEROS
                'brand' => $currentReference ? $currentReference->brand->name : '-',     // <--- INFO PARA COORDINADOR
                'shift' => $activeReport ? $activeReport->shift->name : '-',
                'coordinator' => $activeReport ? $activeReport->coordinator->name : '-',
                'operator' => $activeReport ? $activeReport->operator->name : '-',
                'last_record' => $lastVariable ? Carbon::parse($lastVariable->recorded_at)->format('g:i A') : '--:--',
                'minutes_since' => $minutesSinceLast,
                'time_since' => $timeSinceHuman,
                'efficiency' => $efficiency,
                'status_color' => $statusColor,
                'status_text' => $statusText,
            ];
        });

        // 2. PARETO DE DESPERDICIOS
        $totalWaste = WasteRecord::whereDate('date', $today)->sum('weight_kg');
        $topDefects = WasteRecord::whereDate('date', $today)
            ->select('cause_comment', DB::raw('SUM(weight_kg) as total'))
            ->groupBy('cause_comment')
            ->orderByDesc('total')
            ->take(3)
            ->get()
            ->map(fn($item) => [
                'name' => \Illuminate\Support\Str::limit($item->cause_comment, 20),
                'total' => (float) $item->total
            ]);

        // 3. ESTADO DE SILOS (AUTONOMÍA)
        $silos = SiloBatch::where('is_active', true)->get()->map(function ($silo) {
            $percentage = $silo->initial_quantity > 0 ? ($silo->current_quantity / $silo->initial_quantity) * 100 : 0;
            return [
                'id' => $silo->id,
                'name' => $silo->silo_name,
                'material' => $silo->quality_check['material_type'] ?? 'Materia Prima',
                'current' => number_format($silo->current_quantity, 0, ',', '.'),
                'percentage' => round($percentage),
                'color' => $percentage < 25 ? 'red' : ($percentage < 50 ? 'yellow' : 'green'),
            ];
        });

        return Inertia::render('Dashboard', [
            'linesStatus' => $linesStatus,
            'stats' => [
                'active_lines' => $linesStatus->where('is_active', true)->count(),
                'total_records' => ProductionReport::whereDate('production_date', $today)->withCount('variables')->get()->sum('variables_count'),
                'total_waste' => number_format($totalWaste, 1, ',', '.'),
                'date_human' => $now->locale('es')->isoFormat('dddd, D [de] MMMM YYYY'),
                'julian_day' => $now->dayOfYear, // <--- REINTEGRADO
                'week_number' => $now->weekOfYear,
                'current_time' => $now->format('g:i A'),
            ],
            'silos' => $silos,
            'topDefects' => $topDefects,
        ]);
    }
}