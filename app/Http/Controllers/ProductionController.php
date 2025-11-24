<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionReport;
use App\Models\Shift;
use App\Models\User;
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
            ->latest('fecha')
            ->paginate(10);

        return Inertia::render('Production/Index', [
            'line' => $line,
            'reports' => $reports
        ]);
    }

    public function create($lineSlug)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        
        // 1. Cargar la configuración maestra de campos para ESTA línea
        // Esto lee lo que pusimos en config/lines.php
        $fields = config("lines.{$lineSlug}.fields");

        if (!$fields) {
            abort(404, "Configuración no encontrada para la línea: $lineSlug");
        }

        return Inertia::render('Production/Create', [
            'line' => $line,
            'fields' => $fields, // ¡Aquí enviamos el esquema del formulario al frontend!
            'shifts' => Shift::all(),
            'coordinators' => User::role('Coordinador')->get(), // Asegúrate de tener roles
            'operators' => User::all(), // O filtrar por operarios
        ]);
    }

    public function store(Request $request, $lineSlug)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        $fields = config("lines.{$lineSlug}.fields");

        // 1. Validaciones
        $request->validate([
            'fecha' => 'required|date', // El input del formulario se sigue llamando 'fecha'
            'turno_id' => 'required|exists:shifts,id',
            'coordinator_id' => 'required|exists:users,id',
            'operator_id' => 'required|exists:users,id',
        ]);

        // 2. Extraer Datos Variables
        $variableData = [];
        foreach ($fields as $field) {
            if ($field['type'] === 'header') continue;
            
            $name = $field['name'];
            if ($request->has($name)) {
                $variableData[$name] = $request->input($name);
            }
        }

        // 3. Guardar Encabezado (CORREGIDO AQUÍ)
        $report = ProductionReport::create([
            'line_id' => $line->id,
            'production_date' => $request->fecha, // Mapeamos 'fecha' (input) a 'production_date' (DB)
            'shift_id' => $request->turno_id,
            'coordinator_id' => $request->coordinator_id,
            'operator_id' => $request->operator_id,
            'status' => 'Running', // El valor por defecto en DB es 'Running' (antes 'Abierto')
        ]);

        // 4. Guardar Variables
        $report->variables()->create([
            'data' => $variableData,
            'observacion' => $request->observacion,
            'recorded_at' => now(),
        ]);

        return Redirect::route('production.index', $lineSlug)
            ->with('success', 'Reporte creado correctamente.');
    }

    public function show($lineSlug, $id)
    {
        $line = ProductionLine::where('slug', $lineSlug)->firstOrFail();
        
        // Cargar el reporte con sus relaciones
        $report = ProductionReport::where('line_id', $line->id)
            ->with(['shift', 'coordinator', 'operator', 'variables'])
            ->findOrFail($id);

        // Obtener la configuración de campos para saber qué etiquetas mostrar
        $fields = config("lines.{$lineSlug}.fields");

        return Inertia::render('Production/Show', [
            'line' => $line,
            'report' => $report,
            'fields' => $fields, // Enviamos la config para renderizar las etiquetas correctas
            // Si hay variables guardadas, las enviamos (asumiendo relación 1 a muchos, tomamos la primera o la más reciente)
            'variableData' => $report->variables->first()?->data ?? [],
            'observacion' => $report->variables->first()?->observacion ?? '',
        ]);
    }

}