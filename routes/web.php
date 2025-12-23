<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\SiloBatchController;
use App\Http\Controllers\Settings\ParameterController;
use App\Http\Controllers\DashboardController; // Importamos el Dashboard
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // 1. Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // 2. LOGÍSTICA / MATERIA PRIMA (Silos)
    // Sacamos esto del grupo 'production' para que la URL sea más limpia
    Route::prefix('materia-prima')->name('silos.')->group(function () {
        Route::get('/', [SiloBatchController::class, 'index'])->name('index');
        Route::post('/', [SiloBatchController::class, 'store'])->name('store');
        Route::put('/{id}/cerrar', [SiloBatchController::class, 'close'])->name('close');
    });

    // MÓDULO DE TRAZABILIDAD
    Route::get('/trazabilidad', [App\Http\Controllers\TraceabilityController::class, 'index'])
        ->name('traceability.index');
    // Grupo de Reprocesos
    Route::prefix('reproceso')->name('waste.')->group(function () {
        Route::get('/', [App\Http\Controllers\WasteController::class, 'index'])->name('index');
        Route::get('/crear', [App\Http\Controllers\WasteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\WasteController::class, 'store'])->name('store');
        Route::get('/imprimir/{id}', [App\Http\Controllers\WasteController::class, 'printLabel'])->name('print');
    });

    Route::get('/parametros', [ParameterController::class, 'index'])->name('parameters.index');
    Route::post('/marcas', [ParameterController::class, 'storeBrand'])->name('brands.store');
    Route::post('/productos', [ParameterController::class, 'storeProduct'])->name('products.store');

    // 3. SISTEMA DE PRODUCCIÓN (MES v2)
    // Prefix: /produccion | Name: production.
    Route::name('production.')->prefix('produccion')->group(function () {

        // --- A. Rutas para Variables de Proceso (Horas Individuales) ---
        Route::post('/reporte/{id}/variable', [ProductionController::class, 'storeVariable'])->name('store-variable');
        Route::put('/variable/{id}', [ProductionController::class, 'updateVariable'])->name('update-variable');
        Route::delete('/variable/{id}', [ProductionController::class, 'destroyVariable'])->name('destroy-variable');

        // --- B. Rutas Dinámicas por Línea (Reportes Completos) ---
        // {lineSlug} captura "linea-a", "linea-b", etc.
        Route::prefix('{lineSlug}')->group(function () {
            Route::get('/', [ProductionController::class, 'index'])->name('index');        // Ver listado
            Route::get('/crear', [ProductionController::class, 'create'])->name('create'); // Formulario nuevo turno
            Route::post('/', [ProductionController::class, 'store'])->name('store');       // Guardar nuevo turno
            Route::get('/{id}', [ProductionController::class, 'show'])->name('show');      // Ver detalles
            
            // Editar y Eliminar Reporte Completo
            Route::put('/{id}', [ProductionController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProductionController::class, 'destroy'])->name('destroy');
        });
    });


    // 4. Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';