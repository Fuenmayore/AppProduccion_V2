<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionReport;
use App\Models\Shift;
use App\Models\Product; 
use App\Models\Brand;
use App\Models\User;
use App\Models\ProcessVariable;
use App\Models\SiloBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class ProductionController extends Controller
{
    /**
     * Listado de reportes por línea
     */
    public function index($lineSlug)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        
        $reports = ProductionReport::where('line_id', $line->id)
            ->with(['shift', 'coordinator', 'operator'])
            ->latest('production_date')
            ->paginate(10);

        return Inertia::render('Production/Index', [
            'line' => $line,
            'reports' => $reports
        ]);
    }

    /**
     * Formulario de nuevo turno
     */
    public function create($lineSlug)
    {
        // 1. Mejora: Seguridad - Solo quienes tengan permiso de crear
        Gate::authorize('crear-reportes');

        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        $fields = config("lines.{$lineSlug}.fields");

        // Lógica de turno automático por horario
        $currentHour = now()->hour;
        $shiftId = match(true) {
            $currentHour >= 6 && $currentHour < 14 => 1,
            $currentHour >= 14 && $currentHour < 22 => 2,
            default => 3,
        };

        // Productos filtrados por línea
        $products = Product::whereJsonContains('allowed_line_ids', (string)$line->id)
            ->orWhereJsonContains('allowed_line_ids', $line->id)
            ->select('id', 'name', 'default_mold', 'brand_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Production/Create', [
            'line' => $line,
            'fields' => $fields,
            'shifts' => Shift::all(),
            'currentShiftId' => $shiftId,
            
            // 2. Mejora: Filtro por roles específicos para dar orden
            'coordinators' => User::role('Coordinador')->get(),
            'operators' => User::role('Pastero')->get(), 
            
            'products' => $products,
            'brands' => Brand::select('id', 'name')->orderBy('name')->get(),
            'activeSilos' => SiloBatch::where('is_active', true)->get(),
        ]);
    }

    /**
     * Guardar nuevo turno
     */
    public function store(Request $request, $lineSlug)
    {
        Gate::authorize('crear-reportes');

        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        $fields = config("lines.{$lineSlug}.fields");

        $request->validate([
            'fecha' => 'required|date',
            'turno_id' => 'required|exists:shifts,id',
            'coordinator_id' => 'required|exists:users,id',
            'operator_id' => 'required|exists:users,id',
            'brand_id' => 'required|exists:brands,id',
            'product_id' => 'required|exists:products,id',
            'hora_inicio' => 'required', // Recibido del reloj real-time
        ]);

        // Guardar Encabezado
        $report = ProductionReport::create([
            'line_id' => $line->id,
            'production_date' => $request->fecha,
            'shift_id' => $request->turno_id,
            'coordinator_id' => $request->coordinator_id,
            'operator_id' => $request->operator_id,
            'status' => 'Running',
        ]);

        // Extraer Datos Variables Técnicos
        $variableData = [];
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'header') continue;
            if ($request->has($field['name'])) {
                $variableData[$field['name']] = $request->input($field['name']);
            }
        }

        // Guardar Variables (Primera Hora)
        $report->variables()->create([
            'data' => $variableData,
            'observacion' => $request->observacion,
            'recorded_at' => $request->hora_inicio,
            'silo_batch_id' => $request->silo_batch_id,
        ]);
        
        // Guardar Referencia Inicial
        $report->references()->create([
            'product_id' => $request->product_id,
            'brand_id' => $request->brand_id,
            'start_time' => $request->hora_inicio,
            'molds_used' => [ 
                'm1' => $request->matricula_molde_1 ?? $request->matricula_molde,
                'm2' => $request->matricula_molde_2 ?? null
            ]
        ]);

        return Redirect::route('production.index', $lineSlug)
            ->with('success', 'Turno iniciado correctamente.');
    }

    /**
     * Ver detalle del turno y gestionar registros
     */
    public function show($lineSlug, $id)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();

        $report = ProductionReport::where('line_id', $line->id)
            ->with([
                'shift', 'coordinator', 'operator', 
                'variables' => fn($q) => $q->orderBy('recorded_at', 'desc'), 
                'variables.siloBatch', 'references.product', 'references.brand'
            ])
            ->findOrFail($id);

        $products = Product::whereJsonContains('allowed_line_ids', (string)$line->id)
            ->orWhereJsonContains('allowed_line_ids', $line->id)
            ->get();

        return Inertia::render('Production/Show', [
            'line' => $line,
            'report' => $report,
            'fields' => config("lines.{$lineSlug}.fields"),
            'variables' => $report->variables,
            'shifts' => Shift::all(),
            // Filtros para edición dentro de Show
            'coordinators' => User::role('Coordinador')->get(),
            'operators' => User::role('Pastero')->get(),
            'activeSilos' => SiloBatch::where('is_active', true)->get(),
            'products' => $products,
            'brands' => Brand::all(),
        ]);
    }

    /**
     * Guardar un nuevo registro horario
     */
    public function storeVariable(Request $request, $reportId)
    {
        Gate::authorize('editar-reportes');

        $report = ProductionReport::findOrFail($reportId);
        $lineSlug = $report->line->slug;
        $fields = config("lines.{$lineSlug}.fields");

        $variableData = [];
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'header') continue;
            if ($request->has($field['name'])) {
                $variableData[$field['name']] = $request->input($field['name']);
            }
        }

        $report->variables()->create([
            'data' => $variableData,
            'observacion' => $request->observacion,
            'recorded_at' => now()->toTimeString(),
            'silo_batch_id' => $request->silo_batch_id, 
        ]);

        return back()->with('success', 'Registro de hora agregado.');
    }

    /**
     * Actualizar encabezado (Solo Admin/Coordinador)
     */
    public function update(Request $request, $lineSlug, $id)
    {
        Gate::authorize('editar-reportes');

        $report = ProductionReport::findOrFail($id);

        $report->update([
            'production_date' => $request->fecha,
            'shift_id' => $request->turno_id,
            'coordinator_id' => $request->coordinator_id,
            'operator_id' => $request->operator_id,
        ]);

        return back()->with('success', 'Encabezado actualizado correctamente.');
    }

    /**
     * Eliminar reporte completo (Exclusivo Super Admin)
     */
    public function destroy($lineSlug, $id)
    {
        Gate::authorize('eliminar-reportes');

        $report = ProductionReport::findOrFail($id);
        $report->delete();

        return Redirect::route('production.index', $lineSlug)
            ->with('success', 'Reporte eliminado correctamente.');
    }

    public function updateVariable(Request $request, $id)
    {
        Gate::authorize('editar-reportes');

        $variable = ProcessVariable::findOrFail($id);
        $lineSlug = $variable->report->line->slug;
        $fields = config("lines.{$lineSlug}.fields");

        $variableData = [];
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'header') continue;
            if ($request->has($field['name'])) {
                $variableData[$field['name']] = $request->input($field['name']);
            }
        }

        $variable->update([
            'data' => $variableData,
            'observacion' => $request->observacion,
        ]);

        return back()->with('success', 'Registro actualizado.');
    }

    public function destroyVariable($id)
    {
        Gate::authorize('editar-reportes');
        $variable = ProcessVariable::findOrFail($id);
        $variable->delete();

        return back()->with('success', 'Registro eliminado.');
    }

    /**
     * Registrar cambio de producto (Referencia)
     */
    public function storeReferenceChange(Request $request, $reportId)
    {
        Gate::authorize('editar-reportes');

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'brand_id'   => 'required|exists:brands,id',
            'start_time' => 'required',
        ]);

        $report = ProductionReport::findOrFail($reportId);

        // Finalizar la referencia actual
        $currentRef = $report->references()->whereNull('end_time')->latest()->first();
        if ($currentRef) {
            $currentRef->update(['end_time' => $request->end_time_previous ?? $request->start_time]);
        }

        // Crear la nueva referencia
        $report->references()->create([
            'product_id' => $request->product_id,
            'brand_id'   => $request->brand_id,
            'start_time' => $request->start_time,
            'molds_used' => [
                'm1' => $request->matricula_molde1,
                'm2' => $request->matricula_molde2
            ]
        ]);

        return back()->with('success', 'Cambio de referencia registrado.');
    }
}