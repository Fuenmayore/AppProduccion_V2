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

        $query = ProductionReport::with([
            'line', 
            'shift', 
            'coordinator', 
            'operator', 
            'variables.siloBatch',
            'references.product'
        ]);

        if ($request->filled('date_start')) {
            $query->whereDate('production_date', '>=', $request->date_start);
        } else {
            $query->whereDate('production_date', '>=', now()->subDays(7));
        }

        if ($request->filled('date_end')) {
            $query->whereDate('production_date', '<=', $request->date_end);
        }

        if ($request->filled('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        if ($request->filled('batch_code')) {
            $query->whereHas('variables.siloBatch', function ($q) use ($request) {
                $q->where('internal_batch_code', 'like', "%{$request->batch_code}%");
            });
        }

        $reports = $query->orderBy('production_date', 'desc')
                         ->orderBy('shift_id', 'asc')
                         ->get()
                         ->map(function ($report) {
                             return [
                                 'id' => $report->id,
                                 'date' => $report->production_date->format('d/m/Y'),
                                 'line' => $report->line->name,
                                 'line_slug' => $report->line->slug, // ✅ DATO CLAVE AGREGADO
                                 'shift' => $report->shift->name,
                                 'coordinator' => $report->coordinator->name,
                                 'operator' => $report->operator->name,
                                 'products' => $report->references->pluck('product.name')->unique()->values(),
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