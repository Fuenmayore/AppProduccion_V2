<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =========================================================================
        // 1. CATÁLOGOS DEL SISTEMA (Configuración Global)
        // =========================================================================

        // Unifica: Lineas, Turnos, Marcas, Recetas, Lugares, Causales, Defectos
        
        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Linea A, Linea B, etc.
            $table->string('slug')->unique();
            $table->json('config')->nullable(); // Configuración específica de la línea
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) { // Turnos
            $table->id();
            $table->string('name'); // Turno 1, 2, 3
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) { // Marcas
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) { // Recetas
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('line_id')->nullable()->constrained('production_lines');
            $table->json('specs')->nullable(); // Especificaciones técnicas (agua, semola, etc)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) { // Unifica ProductoA, B, C, D
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            // Array de IDs de líneas donde se puede fabricar: [1, 2]
            $table->json('allowed_line_ids')->nullable(); 
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) { // Lugares (para desperdicios)
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('waste_types', function (Blueprint $table) { // Tipo Desperdicio/Reproceso
            $table->id();
            $table->string('name'); // Barrido, Pasta Húmeda, etc.
            $table->string('category'); // 'waste' (desperdicio) o 'rework' (reproceso)
            $table->timestamps();
        });

        Schema::create('printers', function (Blueprint $table) { // Impresoras
            $table->id();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });


        // =========================================================================
        // 2. LOGÍSTICA Y MATERIA PRIMA (Entradas)
        // =========================================================================

        // Unifica InspeccionVehiculo y MateriaPrima
        Schema::create('logistics_entries', function (Blueprint $table) {
            $table->id();
            $table->string('transport_company')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('trailer_plate')->nullable();
            $table->string('remission_number')->nullable(); // Remisión
            $table->string('origin')->nullable(); // Procedencia
            
            // Datos de Inspección (Checklist de limpieza, plagas, etc en JSON)
            // Ejemplo: {"olores": "ok", "plagas": "no", "limpieza": "ok"}
            $table->json('inspection_checklists')->nullable(); 
            
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('inspector_id')->constrained('users'); // Quién inspeccionó
            $table->timestamps();
        });

        // Detalle de materia prima recibida en esa entrada
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_entry_id')->constrained()->onDelete('cascade');
            $table->string('material_type'); // Harina, Semolato, etc.
            $table->string('batch'); // Lote proveedor
            $table->decimal('quantity', 10, 2);
            $table->string('unit'); // Kg, Ton
            
            // Análisis de Laboratorio (Humedad, Proteína, etc.)
            // Guardado en JSON para flexibilidad entre tipos de harina
            $table->json('lab_results')->nullable(); 
            
            $table->timestamps();
        });

        // Unifica Llenado_Silos
        Schema::create('silo_batches', function (Blueprint $table) {
            $table->id();
            $table->string('silo_name'); // 1A, 1B, 1C...
            $table->string('internal_batch_code')->unique(); // Lote interno generado
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials');
            $table->date('loading_date');
            $table->foreignId('operator_id')->constrained('users');
            
            // Checklist cualitativo (Magnetos, Cribas) en JSON
            $table->json('quality_check')->nullable(); 
            
            $table->boolean('is_active')->default(true); // Si el silo tiene este lote actualmente
            $table->timestamps();
        });


        // =========================================================================
        // 3. NÚCLEO DE PRODUCCIÓN (El corazón del sistema)
        // =========================================================================

        // Unifica Linea_A, Linea_B, Linea_C, Linea_D
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('production_lines');
            $table->date('production_date');
            $table->foreignId('shift_id')->constrained('shifts'); // Turno
            
            // Personal
            $table->foreignId('coordinator_id')->nullable()->constrained('users');
            $table->foreignId('operator_id')->nullable()->constrained('users');
            
            $table->enum('status', ['Running', 'Completed', 'Verified'])->default('Running');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            
            $table->text('general_observations')->nullable();
            $table->timestamps();
        });

        // Unifica ACaracteristicas, BCaracteristicas, C, D...
        // Esta es la tabla mas importante para la optimización
        Schema::create('process_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
            $table->time('recorded_at');
            
            // Relación con el silo que se está usando
            $table->foreignId('silo_batch_id')->nullable()->constrained('silo_batches');
            $table->foreignId('silo_batch_2_id')->nullable()->constrained('silo_batches'); // Para mezclas
            $table->integer('mix_percentage')->nullable(); // % de mezcla

            // ---------------------------------------------------------
            // COLUMNA MÁGICA: Aquí van Trabatto, Presecado, Temperaturas,
            // Velocidades, Presiones, Vacío, etc.
            // La estructura depende de la Línea (A, B, C, D) definida en config.
            // ---------------------------------------------------------
            $table->json('data'); 

            $table->string('cleaning_image_path')->nullable(); // img_limpieza
            $table->timestamps();
        });

        // Unifica ACambioReferencias, BCambio...
        Schema::create('reference_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
            
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('brand_id')->constrained('brands');
            $table->foreignId('recipe_id')->nullable()->constrained('recipes');
            
            $table->time('start_time');
            $table->time('end_time')->nullable();

            // Moldes usados (Matricula 1, Matricula 2...)
            // JSON porque algunas líneas usan 1 molde, otras 2.
            $table->json('molds_used')->nullable(); 
            
            $table->timestamps();
        });


        // =========================================================================
        // 4. CONTROL DE CALIDAD Y SALIDA (Producto Terminado)
        // =========================================================================

        // Unifica ControlChapas, ControlSalidaProducto, PesosMartinis
        Schema::create('finished_product_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
            
            // Tipo de control: 'weight_check' (peso), 'visual_check' (chapas), 'moisture_check' (humedad pt)
            $table->string('check_type'); 
            
            // Los resultados específicos de cada test
            // Ej Peso: {"peso_promedio": 500, "min": 498, "max": 502}
            // Ej Chapas: {"fuga": false, "sellado": "ok"}
            $table->json('results'); 
            
            $table->foreignId('user_id')->constrained('users'); // Quién hizo el chequeo
            $table->timestamps();
        });
        
        // Empaque / Salida de Producto
        Schema::create('packaging_outputs', function (Blueprint $table) {
             $table->id();
             $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
             $table->foreignId('product_id')->constrained('products');
             
             $table->integer('units_produced');
             $table->integer('boxes_produced')->nullable();
             $table->integer('pallets_produced')->nullable();
             
             // Consumo de material de empaque (Laminado, Cajas, etc)
             $table->json('packaging_materials_consumed')->nullable();
             
             $table->timestamps();
        });


        // =========================================================================
        // 5. DESPERDICIOS Y REPROCESOS (Eficiencia)
        // =========================================================================

        // Unifica Excedentes_industriales y Reprocesos
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('line_id')->nullable()->constrained('production_lines'); // Opcional, puede ser general
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            
            $table->foreignId('waste_type_id')->constrained('waste_types'); // Qué tipo es
            $table->foreignId('location_id')->nullable()->constrained('locations'); // Dónde ocurrió
            $table->foreignId('product_id')->nullable()->constrained('products'); // Qué producto se perdió
            
            $table->decimal('weight_kg', 10, 2);
            $table->text('cause_comment')->nullable(); // Causa raíz
            $table->foreignId('user_id')->constrained('users'); // Responsable
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
        Schema::dropIfExists('packaging_outputs');
        Schema::dropIfExists('finished_product_checks');
        Schema::dropIfExists('reference_changes');
        Schema::dropIfExists('process_variables');
        Schema::dropIfExists('production_reports');
        Schema::dropIfExists('silo_batches');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('logistics_entries');
        Schema::dropIfExists('printers');
        Schema::dropIfExists('waste_types');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('production_lines');
    }
};