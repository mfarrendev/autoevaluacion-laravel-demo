<?php

use App\Http\Controllers\AutoevaluacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AutoevaluacionController::class, 'inicio'])->name('inicio');
Route::post('/autoevaluaciones', [AutoevaluacionController::class, 'crear'])->name('autoevaluaciones.crear');
Route::get('/autoevaluaciones/{autoevaluacion}', [AutoevaluacionController::class, 'mostrar'])->name('autoevaluaciones.mostrar');
Route::put('/autoevaluaciones/{autoevaluacion}', [AutoevaluacionController::class, 'guardar'])->name('autoevaluaciones.guardar');
Route::get('/autoevaluaciones/{autoevaluacion}/reporte', [AutoevaluacionController::class, 'reporte'])->name('autoevaluaciones.reporte');
