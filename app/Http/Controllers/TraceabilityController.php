<?php

namespace App\Http\Controllers;

use App\Models\ProductionReport;
use App\Models\ProductionLine;
use App\Models\SiloBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->all('date_start', 'date_end', 'line_id', 'batch_code');

        // Consulta Base: Reportes de Producción con todas sus relaciones
        $query = ProductionReport::with([
            'line', 
            'shift', 
            'coordinator', 
            'operator', 
            'variables.siloBatch', // Para ver qué lote de silo se usó
            'references.product'   // Para ver qué producto se hizo
        ]);

        // 1. Filtro por Fechas (Por defecto: Hoy)
        if ($request->filled('date_start')) {
            $query->whereDate('production_date', '>=', $request->date_start);
        } else {
            // Si no hay búsqueda, mostramos últimos 7 días por defecto
            $query->whereDate('production_date', '>=', now()->subDays(7));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('production_date', '<=', $request->date_end);
        }

        // 2. Filtro por Línea
        if ($request->filled('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        // 3. Filtro por Lote de Materia Prima (Trazabilidad hacia atrás)
        if ($request->filled('batch_code')) {
            $query->whereHas('variables.siloBatch', function ($q) use ($request) {
                $q->where('internal_batch_code', 'like', "%{$request->batch_code}%");
            });
        }

        // Ordenar cronológicamente
        $reports = $query->orderBy('production_date', 'desc')
                         ->orderBy('shift_id', 'asc')
                         ->get()
                         ->map(function ($report) {
                             // Procesamos los datos para facilitar la vista
                             return [
                                 'id' => $report->id,
                                 'date' => $report->production_date->format('d/m/Y'),
                                 'line' => $report->line->name,
                                 'shift' => $report->shift->name,
                                 'coordinator' => $report->coordinator->name,
                                 'operator' => $report->operator->name,
                                 // Extraemos los productos únicos fabricados en ese turno
                                 'products' => $report->references->pluck('product.name')->unique()->values(),
                                 // Extraemos los lotes de materia prima usados
                                 'raw_materials' => $report->variables->pluck('siloBatch.internal_batch_code')->unique()->filter()->values(),
                                 'variable_count' => $report->variables->count(),
                                 'status' => $report->status,
                             ];
                         });

        return Inertia::render('Traceability/Index', [
            'filters' => $filters,
            'reports' => $reports,
            'lines' => ProductionLine::select('id', 'name')->get(),
        ]);
    }
}