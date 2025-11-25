<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatronController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EmpleadoPeriodoController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/patrons', PatronController::class);

    Route::resource('/supervisors', SupervisorController::class);

    Route::resource('/sucursals', SucursalController::class);

    Route::resource('/departamentos', DepartamentoController::class);

    Route::patch('/empleados/{empleado}/estado', [EmpleadoController::class, 'cambiarEstado'])
    ->name('empleados.cambiarEstado');

    Route::resource('/empleados', EmpleadoController::class);

    // Periodos de un empleado específico (colección)
    Route::get('/empleados/{empleado}/periodos', [EmpleadoPeriodoController::class, 'index'])
        ->name('empleados.periodos.index');

    Route::post('/empleados/{empleado}/periodos', [EmpleadoPeriodoController::class, 'store'])
        ->name('empleados.periodos.store');

    // Operaciones sobre un periodo específico
    Route::get('/periodos/{periodo}', [EmpleadoPeriodoController::class, 'show'])
        ->name('periodos.show');

    Route::put('/periodos/{periodo}', [EmpleadoPeriodoController::class, 'update'])
        ->name('periodos.update');

    Route::patch('/periodos/{periodo}', [EmpleadoPeriodoController::class, 'update'])
        ->name('periodos.patch');

    Route::delete('/periodos/{periodo}', [EmpleadoPeriodoController::class, 'destroy'])
        ->name('periodos.destroy');

    // 🔹 Reportes
    Route::get('/reportes', [ReporteController::class, 'index'])
        ->name('reportes.index');

    // Exportación a Excel (la que pide reportes.export)
    Route::get('/reportes/export', [ReporteController::class, 'export'])
        ->name('reportes.export');

    // 🔹 Base de datos (carga de datos y respaldo)
    // Vista principal 
    Route::get('/backup', [BackupController::class, 'index'])
        ->name('backup.index');

    // Descargar respaldo
    Route::get('/backup/descargar', [BackupController::class, 'download'])
        ->name('backup.download');

    // Restaurar desde archivo SQL
    Route::post('/backup/restaurar', [BackupController::class, 'restore'])
        ->name('backup.restore');

});

require __DIR__.'/auth.php';
