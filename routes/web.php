<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\SiloBatchController;
use App\Http\Controllers\Settings\ParameterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController; // Controlador de Usuarios
use App\Http\Controllers\Admin\RoleController; // Controlador de Roles
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\WasteController;
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
    Route::prefix('materia-prima')->name('silos.')->group(function () {
        Route::get('/', [SiloBatchController::class, 'index'])->name('index');
        Route::post('/', [SiloBatchController::class, 'store'])->name('store');
        Route::put('/{id}/cerrar', [SiloBatchController::class, 'close'])->name('close');
    });

    // 3. CALIDAD Y TRAZABILIDAD
    Route::get('/trazabilidad', [TraceabilityController::class, 'index'])->name('traceability.index');
    
    Route::prefix('reproceso')->name('waste.')->group(function () {
        Route::get('/', [WasteController::class, 'index'])->name('index');
        Route::get('/crear', [WasteController::class, 'create'])->name('create');
        Route::post('/', [WasteController::class, 'store'])->name('store');
        Route::get('/imprimir/{id}', [WasteController::class, 'printLabel'])->name('print');
    });

    // 4. PARÁMETROS (Marcas y Productos)
    // Protegido opcionalmente por permiso en el controlador o middleware
    Route::prefix('configuracion')->group(function () {
        Route::get('/parametros', [ParameterController::class, 'index'])->name('parameters.index');
        Route::post('/marcas', [ParameterController::class, 'storeBrand'])->name('brands.store');
        Route::post('/productos', [ParameterController::class, 'storeProduct'])->name('products.store');
    });

    // 5. SISTEMA DE PRODUCCIÓN (MES)
    Route::name('production.')->prefix('produccion')->group(function () {

        // Rutas globales para registros horarios (Variables)
        Route::post('/reporte/{id}/variable', [ProductionController::class, 'storeVariable'])->name('store-variable');
        Route::put('/variable/{id}', [ProductionController::class, 'updateVariable'])->name('update-variable');
        Route::delete('/variable/{id}', [ProductionController::class, 'destroyVariable'])->name('destroy-variable');
        
        // Cambio de referencia
        Route::post('/reporte/{id}/cambio-referencia', [ProductionController::class, 'storeReferenceChange'])->name('store-reference');

        // Rutas Dinámicas por Línea ({lineSlug}: linea-a, linea-b...)
        Route::prefix('{lineSlug}')->group(function () {
            Route::get('/', [ProductionController::class, 'index'])->name('index');
            Route::get('/crear', [ProductionController::class, 'create'])->name('create');
            Route::post('/', [ProductionController::class, 'store'])->name('store');
            Route::get('/{id}', [ProductionController::class, 'show'])->name('show');
            Route::put('/{id}', [ProductionController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProductionController::class, 'destroy'])->name('destroy');
        });
    });

    // 6. PERFIL DE USUARIO (Propio)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 7. ADMINISTRACIÓN DE USUARIOS (Solo Super Admin)
    // Usamos el alias 'role' definido en bootstrap/app.php
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update'); // Nueva: Editar datos y rol
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Gestión de Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

});

require __DIR__ . '/auth.php';