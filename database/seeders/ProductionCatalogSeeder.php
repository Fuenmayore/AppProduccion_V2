<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionLine;
use App\Models\Shift;
use App\Models\Brand;

class ProductionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear las Líneas (Basado en tu LineaSeeder)
        $lines = [
            ['name' => 'Línea A', 'slug' => 'linea-a'],
            ['name' => 'Línea B', 'slug' => 'linea-b'],
            ['name' => 'Línea C', 'slug' => 'linea-c'],
            ['name' => 'Línea D', 'slug' => 'linea-d'],
        ];

        foreach ($lines as $line) {
            ProductionLine::firstOrCreate(['slug' => $line['slug']], $line);
        }

        // 2. Crear los Turnos (Basado en tu TurnoSeeder)
        // Ajusta las horas exactas si difieren
        $shifts = [
            ['name' => 'Turno 1', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Turno 2', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Turno 3', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }

        // 3. Crear Marcas (Basado en MarcasSeeder)
        $brands = ['Comarrico', 'Bonia', 'D1', 'Suprema']; // Agrega las que faltan
        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand]);
        }
    }
}