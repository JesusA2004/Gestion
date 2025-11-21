<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatronController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\SupervisorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

});

require __DIR__.'/auth.php';
