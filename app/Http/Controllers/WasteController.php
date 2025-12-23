<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\Shift;
use App\Models\WasteRecord; // Recuerda crear este modelo si no existe
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class WasteController extends Controller
{
    public function index()
    {
        return Inertia::render('Waste/Index', [
            'records' => WasteRecord::with(['line', 'product', 'operator'])
                ->latest('date')
                ->paginate(15),
            'lines' => ProductionLine::select('id', 'name', 'slug')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Waste/Create', [
            'lines' => ProductionLine::select('id', 'name')->get(),
            'shifts' => Shift::all(),
            'products' => Product::select('id', 'name')->orderBy('name')->get(),
            // Cargamos las opciones desde config
            'types' => config('lines.waste_settings.rework_types'), 
            'locations' => config('lines.waste_settings.locations'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'line_id' => 'required|exists:production_lines,id',
            'shift_id' => 'required|exists:shifts,id',
            'product_id' => 'required|exists:products,id',
            'waste_type' => 'required|string', // Guardamos el nombre directo o ID si usas tabla
            'location' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
        ]);

        $record = WasteRecord::create([
            'date' => $request->date,
            'line_id' => $request->line_id,
            'shift_id' => $request->shift_id,
            'product_id' => $request->product_id,
            // En la migración unificada usamos 'waste_type_id', 
            // pero para simplificar aquí guardaremos el texto en 'cause_comment' o 
            // idealmente ajustaríamos el modelo para aceptar texto o crear el tipo dinámicamente.
            // Asumiremos que ajustaste el modelo para guardar el tipo como string o tienes los IDs.
            'waste_type_id' => 1, // OJO: Ajustar según tu DB real de tipos
            'location_id' => 1,   // OJO: Ajustar según tu DB real de lugares
            'weight_kg' => $request->weight_kg,
            'cause_comment' => $request->observacion . " | Tipo: " . $request->waste_type . " | Lugar: " . $request->location,
            'user_id' => Auth::id(),
        ]);

        // Redirigir con datos para impresión automática si se desea
        return redirect()->route('waste.index')
            ->with('success', 'Reproceso registrado correctamente.')
            ->with('print_id', $record->id); // Flag para activar impresión
    }

    // Método para generar la etiqueta ZPL o HTML
    public function printLabel($id)
    {
        $record = WasteRecord::with(['line', 'product', 'shift', 'operator'])->findOrFail($id);

        // Opción A: Vista HTML simple para imprimir (Más compatible)
        return view('print.sticker', compact('record'));

        // Opción B: Código ZPL directo (Para impresoras Zebra industriales)
        // $zpl = "^XA^FO50,50^ADN,36,20^FDReproceso^FS...";
        // return response($zpl)->header('Content-Type', 'text/plain');
    }
}