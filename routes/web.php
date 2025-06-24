<?php

use App\Http\Controllers\AplicacionController;
use Illuminate\Support\Facades\Route;

Route::get('/restablecer-contrasena/{token}', [AplicacionController::class, 'index'])->name('password.reset');

Route::fallback([AplicacionController::class, 'index']);