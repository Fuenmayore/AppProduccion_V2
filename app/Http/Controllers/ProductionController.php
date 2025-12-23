<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionReport;
use App\Models\Shift;
use App\Models\Product; 
use App\Models\Brand; // Importado para las marcas
use App\Models\User;
use App\Models\ProcessVariable;
use App\Models\SiloBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ProductionController extends Controller
{
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

    public function create($lineSlug)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        $fields = config("lines.{$lineSlug}.fields");

        // 1. LÓGICA DE TURNO AUTOMÁTICO
        $currentHour = now()->hour;
        $shiftId = match(true) {
            $currentHour >= 6 && $currentHour < 14 => 1,
            $currentHour >= 14 && $currentHour < 22 => 2,
            default => 3,
        };

        // 2. OBTENER PRODUCTOS (REFERENCIAS) FILTRADOS POR LÍNEA
        $products = Product::whereJsonContains('allowed_line_ids', (string)$line->id)
            ->orWhereJsonContains('allowed_line_ids', $line->id)
            ->select('id', 'name', 'default_mold', 'brand_id')
            ->orderBy('name')
            ->get();

        // 3. OBTENER MARCAS (PARÁMETROS CONFIGURABLES)
        $brands = Brand::orderBy('name')->get();

        // 4. OBTENER LOTES DE SILOS ACTIVOS
        $activeSilos = SiloBatch::where('is_active', true)
            ->with('rawMaterial:id,material_type')
            ->get();

        return Inertia::render('Production/Create', [
            'line' => $line,
            'fields' => $fields,
            'shifts' => Shift::all(),
            'currentShiftId' => $shiftId,
            'coordinators' => User::role('Coordinador')->get(),
            'operators' => User::role('Operario')->get(), // Filtrado por rol
            'products' => $products,
            'brands' => $brands, // Enviamos las marcas para el selector
            'activeSilos' => $activeSilos,
        ]);
    }

    public function store(Request $request, $lineSlug)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        $fields = config("lines.{$lineSlug}.fields");

        // 1. Validaciones
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required', // Validamos la hora de inicio automática
            'turno_id' => 'required|exists:shifts,id',
            'coordinator_id' => 'required|exists:users,id',
            'operator_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'brand_id' => 'required|exists:brands,id', // Validamos la marca
            'silo_batch_id' => 'nullable|exists:silo_batches,id',
        ]);

        // 2. Extraer Datos Variables Técnicos
        $variableData = [];
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'header') continue;
            $name = $field['name'];
            if ($request->has($name)) {
                $variableData[$name] = $request->input($name);
            }
        }

        // 3. Guardar Encabezado del Reporte
        $report = ProductionReport::create([
            'line_id' => $line->id,
            'production_date' => $request->fecha,
            'shift_id' => $request->turno_id,
            'coordinator_id' => $request->coordinator_id,
            'operator_id' => $request->operator_id,
            'status' => 'Running',
        ]);

        // 4. Guardar Variables (Registro de la primera hora)
        $report->variables()->create([
            'data' => $variableData,
            'observacion' => $request->observacion,
            'recorded_at' => $request->hora_inicio, // Usamos la hora de inicio para el primer registro
            'silo_batch_id' => $request->silo_batch_id,
        ]);
        
        // 5. Guardar Referencia de Producción e Inicio de Tiempos
        $report->references()->create([
            'product_id' => $request->product_id,
            'brand_id' => $request->brand_id, // Marca seleccionada
            'start_time' => $request->hora_inicio, // Hora exacta del registro
            'molds_used' => [ 
                'm1' => $request->matricula_molde_1 ?? $request->matricula_molde,
                'm2' => $request->matricula_molde_2 ?? null
            ]
        ]);

        return Redirect::route('production.index', $lineSlug)
            ->with('success', 'Reporte e inicio de turno registrados correctamente.');
    }

    public function show($lineSlug, $id)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();

        $report = ProductionReport::where('line_id', $line->id)
            ->with(['shift', 'coordinator', 'operator', 'variables' => function ($q) {
                $q->orderBy('recorded_at', 'desc');
            }])
            ->findOrFail($id);

        $fields = config("lines.{$lineSlug}.fields");

        return Inertia::render('Production/Show', [
            'line' => $line,
            'report' => $report,
            'fields' => $fields,
            'variables' => $report->variables,
            'shifts' => Shift::all(),
            'coordinators' => User::role('Coordinador')->get(),
            'operators' => User::all(),
        ]);
    }

    public function storeVariable(Request $request, $reportId)
    {
        $report = ProductionReport::findOrFail($reportId);
        $lineSlug = $report->line->slug;
        $fields = config("lines.{$lineSlug}.fields");

        $variableData = [];
        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'header') continue;
            $name = $field['name'];
            if ($request->has($name)) {
                $variableData[$name] = $request->input($name);
            }
        }

        $report->variables()->create([
            'data' => $variableData,
            'observacion' => $request->observacion,
            'recorded_at' => now()->toTimeString(),
        ]);

        return back()->with('success', 'Registro de hora agregado.');
    }

    // ... Otros métodos (update, destroy, etc) se mantienen igual ...


    // --- ACCIONES DE REPORTE (ENCABEZADO) ---

    public function update(Request $request, $lineSlug, $id)
    {
        $report = ProductionReport::findOrFail($id);

        $request->validate([
            'fecha' => 'required|date', // Validamos el input 'fecha'
            'turno_id' => 'required|exists:shifts,id',
            'coordinator_id' => 'required|exists:users,id',
            'operator_id' => 'required|exists:users,id',
        ]);

        $report->update([
            'production_date' => $request->fecha, // Mapeo al nombre en DB
            'shift_id' => $request->turno_id,
            'coordinator_id' => $request->coordinator_id,
            'operator_id' => $request->operator_id,
        ]);

        return back()->with('success', 'Encabezado actualizado correctamente.');
    }

    public function destroy($lineSlug, $id)
    {
        $report = ProductionReport::findOrFail($id);
        $report->delete(); // Borra en cascada las variables si está configurado en DB

        return Redirect::route('production.index', $lineSlug)
            ->with('success', 'Reporte eliminado correctamente.');
    }

    // --- ACCIONES DE VARIABLES (HORAS) ---

    public function updateVariable(Request $request, $id)
    {
        $variable = ProcessVariable::findOrFail($id);
        $lineSlug = $variable->report->line->slug;
        $fields = config("lines.{$lineSlug}.fields");

        $variableData = [];
        foreach ($fields as $field) {
            if ($field['type'] === 'header') continue;
            $name = $field['name'];
            if ($request->has($name)) {
                $variableData[$name] = $request->input($name);
            }
        }

        $variable->update([
            'data' => $variableData,
            'observacion' => $request->observacion,
            // No actualizamos recorded_at para mantener la hora original, 
            // o puedes agregar un input de hora si quieres permitir editarla.
        ]);

        return back()->with('success', 'Registro actualizado.');
    }

    public function destroyVariable($id)
    {
        $variable = ProcessVariable::findOrFail($id);
        $variable->delete();

        return back()->with('success', 'Registro eliminado.');
    }
}
