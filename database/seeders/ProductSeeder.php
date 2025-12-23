<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // IDs de Líneas (según el orden de creación en ProductionCatalogSeeder):
        // 1: Línea A (Cortas)
        // 2: Línea B (Largas)
        // 3: Línea C (Especialidades)
        // 4: Línea D (Cortas/Otras)

        $products = [
            // --- LÍNEA A (Figuras Cortas) ---
            ['name' => 'Macarrón Corto', 'default_mold' => 'M-101', 'allowed_line_ids' => [1, 4]], 
            ['name' => 'Conchitas',      'default_mold' => 'M-102', 'allowed_line_ids' => [1]],
            ['name' => 'Fideo Cortado',  'default_mold' => 'M-103', 'allowed_line_ids' => [1]],

            // --- LÍNEA B (Figuras Largas) ---
            ['name' => 'Spaghetti 250g', 'default_mold' => 'M-201', 'allowed_line_ids' => [2]],
            ['name' => 'Spaghetti 500g', 'default_mold' => 'M-202', 'allowed_line_ids' => [2]],
            ['name' => 'Tallarines',     'default_mold' => 'M-203', 'allowed_line_ids' => [2]],

            // --- LÍNEA C (Especialidades) ---
            ['name' => 'Lasagna',        'default_mold' => 'M-301', 'allowed_line_ids' => [3]],
            ['name' => 'Nidos Spinaci',  'default_mold' => 'M-302', 'allowed_line_ids' => [3]],
            
            // --- LÍNEA D ---
            ['name' => 'Penne Rigate',   'default_mold' => 'M-401', 'allowed_line_ids' => [4]],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}