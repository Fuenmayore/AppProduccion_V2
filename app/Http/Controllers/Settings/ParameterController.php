<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductionLine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParameterController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Parameters', [
            'brands' => Brand::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
            'productionLines' => ProductionLine::all()
        ]);
    }

    public function storeBrand(Request $request)
    {
        Brand::create($request->validate(['name' => 'required|unique:brands']));
        return back()->with('success', 'Marca registrada.');
    }

 public function storeProduct(Request $request)
{
    $request->validate([
        'name' => 'required|unique:products|max:255',
        'brand_id' => 'required|exists:brands,id', // <--- Validación obligatoria
        'allowed_line_ids' => 'required|array',
        'default_mold' => 'nullable'
    ]);

    // Usar $request->all() para incluir el brand_id
    Product::create($request->all());

    return back()->with('success', 'Referencia registrada correctamente.');
}
}
