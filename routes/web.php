<?php

use App\Http\Controllers\AgenteController;
use App\Http\Controllers\AuxiliarController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\RentaController;
use App\Http\Controllers\SeguroController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $site = json_decode(file_get_contents(resource_path('content.json')), true);

    return Inertia::render('Home', ['site' => $site]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ----- Módulo de Seguros -----
    Route::resource('agentes', AgenteController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['agentes' => 'agente']);
    Route::resource('seguros', SeguroController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['seguros' => 'seguro']);
    Route::resource('cotizaciones', CotizacionController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['cotizaciones' => 'cotizacion']);
    Route::put('cotizaciones/{cotizacion}/seleccionar', [CotizacionController::class, 'seleccionar'])
        ->name('cotizaciones.seleccionar');

    // ----- Módulo de Rentas -----
    Route::resource('propiedades', PropiedadController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['propiedades' => 'propiedad']);
    Route::resource('inquilinos', InquilinoController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['inquilinos' => 'inquilino']);
    Route::resource('rentas', RentaController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['rentas' => 'renta']);
    Route::put('rentas/{renta}/cobrar', [RentaController::class, 'cobrar'])
        ->name('rentas.cobrar');

    // ----- Módulo de Auxiliar Bancario -----
    Route::resource('proyectos', ProyectoController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['proyectos' => 'proyecto']);
    Route::resource('auxiliares', AuxiliarController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['auxiliares' => 'auxiliar']);
    Route::resource('movimientos', MovimientoController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['movimientos' => 'movimiento']);
});

require __DIR__.'/auth.php';
