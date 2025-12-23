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

        // 1.1 Líneas de Producción
        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Línea A"
            $table->string('slug')->unique(); // "linea-a"
            $table->json('config')->nullable(); 
            $table->timestamps();
        });

        // 1.2 Turnos
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Turno 1"
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        // 1.3 Marcas
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 1.4 Recetas
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('line_id')->nullable()->constrained('production_lines');
            $table->json('specs')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 1.5 Productos (CORREGIDO: Incluye Brand ID para configuración)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade'); // Relación directa con marca
            $table->string('default_mold')->nullable(); // Matrícula sugerida configurada
            $table->string('sku')->nullable();
            $table->json('allowed_line_ids')->nullable(); // Líneas donde se permite fabricar
            $table->timestamps();
        });

        // 1.6 Lugares, Tipos de Desperdicio e Impresoras
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('waste_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('category'); // 'waste' o 'rework'
            $table->timestamps();
        });

        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // =========================================================================
        // 2. LOGÍSTICA Y MATERIA PRIMA
        // =========================================================================

        Schema::create('logistics_entries', function (Blueprint $table) {
            $table->id();
            $table->string('transport_company')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('trailer_plate')->nullable();
            $table->string('remission_number')->nullable();
            $table->string('origin')->nullable();
            $table->json('inspection_checklists')->nullable(); 
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('inspector_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_entry_id')->constrained('logistics_entries')->onDelete('cascade');
            $table->string('material_type'); 
            $table->string('batch'); 
            $table->decimal('quantity', 10, 2);
            $table->string('unit'); 
            $table->json('lab_results')->nullable(); 
            $table->timestamps();
        });

        Schema::create('silo_batches', function (Blueprint $table) {
            $table->id();
            $table->string('silo_name'); 
            $table->string('internal_batch_code')->unique(); 
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials');
            $table->date('loading_date');
            $table->foreignId('operator_id')->constrained('users');
            $table->json('quality_check')->nullable(); 
            $table->decimal('initial_quantity', 10, 2);
            $table->decimal('current_quantity', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =========================================================================
        // 3. NÚCLEO DE PRODUCCIÓN (Reportes y Variables)
        // =========================================================================

        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('production_lines');
            $table->date('production_date');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->foreignId('coordinator_id')->nullable()->constrained('users');
            $table->foreignId('operator_id')->nullable()->constrained('users');
            $table->enum('status', ['Running', 'Completed', 'Verified'])->default('Running');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('general_observations')->nullable();
            $table->timestamps();
        });

        Schema::create('process_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained('production_reports')->onDelete('cascade');
            $table->time('recorded_at'); // Aquí queda grabada la hora de cada registro técnico
            $table->foreignId('silo_batch_id')->nullable()->constrained('silo_batches');
            $table->foreignId('silo_batch_2_id')->nullable()->constrained('silo_batches');
            $table->integer('mix_percentage')->nullable();
            $table->json('data'); 
            $table->text('observacion')->nullable(); 
            $table->string('cleaning_image_path')->nullable(); 
            $table->timestamps();
        });

        Schema::create('reference_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained('production_reports')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('brand_id')->constrained('brands');
            $table->foreignId('recipe_id')->nullable()->constrained('recipes');
            $table->time('start_time'); // Esta es tu "Hora Inicio" automática del reporte
            $table->time('end_time')->nullable();
            $table->json('molds_used')->nullable(); 
            $table->timestamps();
        });

        // =========================================================================
        // 4. CONTROL DE CALIDAD Y SALIDA
        // =========================================================================

        Schema::create('finished_product_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained('production_reports')->onDelete('cascade');
            $table->string('check_type');
            $table->json('results'); 
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
        
        Schema::create('packaging_outputs', function (Blueprint $table) {
             $table->id();
             $table->foreignId('production_report_id')->constrained('production_reports')->onDelete('cascade');
             $table->foreignId('product_id')->constrained('products');
             $table->integer('units_produced');
             $table->integer('boxes_produced')->nullable();
             $table->integer('pallets_produced')->nullable();
             $table->json('packaging_materials_consumed')->nullable();
             $table->timestamps();
        });

        // =========================================================================
        // 5. DESPERDICIOS
        // =========================================================================

        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('line_id')->nullable()->constrained('production_lines');
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->foreignId('waste_type_id')->constrained('waste_types');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->decimal('weight_kg', 10, 2);
            $table->text('cause_comment')->nullable();
            $table->foreignId('user_id')->constrained('users');
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