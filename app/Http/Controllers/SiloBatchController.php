<?php

namespace App\Http\Controllers;

use App\Models\SiloBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiloBatchController extends Controller
{
    public function index()
    {
        return Inertia::render('Silos/Index', [
            'batches' => SiloBatch::where('is_active', true)->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'silo_name' => 'required|string',
            'material_type' => 'required|string',
            'batch_code' => 'required|string',
            'quantity' => 'required|numeric|min:1', // Validar que sea numérico
        ]);

        SiloBatch::create([
            'silo_name' => $request->silo_name,
            'internal_batch_code' => $request->batch_code . '-' . now()->format('dmY'),
            'loading_date' => now(),
            'operator_id' => auth()->id(),
            'is_active' => true,
            
            // --- ASIGNACIÓN DE CANTIDADES ---
            // Al inicio, la cantidad actual es igual a la inicial
            'initial_quantity' => $request->quantity,
            'current_quantity' => $request->quantity,
            
            // Opcional: Guardar el tipo de material en el JSON quality_check si no usas tabla RawMaterials
            'quality_check' => ['material_type' => $request->material_type], 
        ]);

        return back()->with('success', 'Lote de silo registrado correctamente.');
    }
    
    public function close($id)
    {
        SiloBatch::where('id', $id)->update([
            'is_active' => false,
            'current_quantity' => 0 // Al cerrar, aseguramos que quede en 0
        ]);
        return back()->with('success', 'Silo marcado como vacío.');
    }
}