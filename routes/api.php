<?php

use App\Http\Controllers\AplicacionController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\FabricanteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/**
 * Rutas para la gestión de los datos META de la aplicación.
 */
Route::prefix('/aplicacion')->group( function () {
    Route::get('/', [AplicacionController::class, 'ver']);
    Route::put('/', [AplicacionController::class, 'editar'])->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::delete('/', [AplicacionController::class, 'eliminar'])->middleware(['auth:sanctum', 'administrador', 'habilitado']);
});


/**
 * Rutas para recuperar y cambiar una contraseña.
 */
Route::group([ 'prefix' => 'restablecer', 'middleware' => 'no.autenticado' ], function () {
    Route::post('/', [UsuarioController::class, 'restablecerContrasena'])->middleware('throttle:5,1');
    Route::put('/{token}', [UsuarioController::class, 'cambiarContrasena'])->middleware('throttle:5,1');
});


/**
 * Rutas para gestionar la cuenta.
 */
Route::group(['prefix' => 'usuarios'], function () {
    Route::get('/', [UsuarioController::class, 'listar'])->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::post('/', [UsuarioController::class, 'registrar'])->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::get('/{id}', [UsuarioController::class, 'ver'])->whereNumber('id')->middleware(['auth:sanctum', 'habilitado']);
    Route::put('/{id}', [UsuarioController::class, 'editar'])->whereNumber('id')->middleware(['auth:sanctum', 'habilitado']);
    Route::delete('/{id}', [UsuarioController::class, 'eliminar'])->whereNumber('id')->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::get('/{id}/restablecer', [UsuarioController::class, 'restablecerContrasena'])->whereNumber('id')->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::put('/{id}/autorizar', [UsuarioController::class, 'autorizar'])->whereNumber('id')->middleware(['auth:sanctum', 'administrador', 'habilitado']);
    Route::get('/{id}/sesiones', [SesionController::class, 'listar'])->whereNumber('id')->middleware(['auth:sanctum', 'habilitado']);
    Route::delete('/{id}/sesiones', [SesionController::class, 'eliminar'])->whereNumber('id')->middleware(['auth:sanctum', 'habilitado'])->name('cerrar.todas');
    Route::delete('/{id}/sesiones/{sid}', [SesionController::class, 'eliminar'])->middleware(['auth:sanctum', 'habilitado']);
    Route::get('/autenticado', [UsuarioController::class, 'ver'])->middleware(['auth:sanctum', 'habilitado'])->name('autenticado');
    Route::delete('/sesion', [SesionController::class, 'eliminar'])->middleware(['auth:sanctum', 'habilitado'])->name('cerrar.actual');
    Route::post('/autenticar', [SesionController::class, 'autenticar'])->middleware(['no.autenticado', 'throttle:15,1']);
});


/**
 * Rutas para gestionar las bodegas.
 */
Route::group([ 'prefix' => 'bodegas', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [BodegaController::class, 'listar']);
    Route::post('/', [BodegaController::class, 'registrar']);
    Route::get('/{id}', [BodegaController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [BodegaController::class, 'editar'])->whereNumber('id');
    Route::delete('/{id}', [BodegaController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Rutas para gestionar los productos.
 */
Route::group([ 'prefix' => 'productos', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [ProductoController::class, 'listar']);
    Route::post('/', [ProductoController::class, 'registrar']);
    Route::get('/{id}', [ProductoController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [ProductoController::class, 'editar'])->whereNumber('id');
    Route::delete('/{id}', [ProductoController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Rutas para gestionar las unidades de los productos.
 */
Route::group([ 'prefix' => 'unidades', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [UnidadController::class, 'listar']);
    Route::post('/', [UnidadController::class, 'registrar']);
    Route::delete('/', [UnidadController::class, 'eliminar']);
    Route::put('/mover', [UnidadController::class, 'mover']);
    Route::put('/baja', [UnidadController::class, 'darDeBaja']);
    Route::get('/{id}', [UnidadController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [UnidadController::class, 'editar'])->whereNumber('id');
    Route::put('/{id}/mover', [UnidadController::class, 'mover'])->whereNumber('id');
    Route::put('/{id}/baja', [UnidadController::class, 'darDeBaja'])->whereNumber('id');
    Route::delete('/{id}', [UnidadController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Ruta para listar los movimientos de una unidad.
 */
Route::get('/movimientos', [MovimientoController::class, 'listar'])->middleware(['auth:sanctum', 'habilitado']);


/**
 * Rutas para gestionar las empresas (registros padre de proveedores y fabricantes).
 */
Route::group([ 'prefix' => 'empresas', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/{id}/proveedor', [ProveedorController::class, 'ver'])->whereNumber('id')->name('empresa');
    Route::get('/{id}/fabricante', [FabricanteController::class, 'ver'])->whereNumber('id')->name('empresa');
});


/**
 * Rutas para gestionar los proveedores.
 */
Route::group([ 'prefix' => 'proveedores', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [ProveedorController::class, 'listar']);
    Route::post('/', [ProveedorController::class, 'registrar']);
    Route::get('/{id}', [ProveedorController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [ProveedorController::class, 'editar'])->whereNumber('id');
    Route::delete('/{id}', [ProveedorController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Rutas para gestionar los fabricantes.
 */
Route::group([ 'prefix' => 'fabricantes', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [FabricanteController::class, 'listar']);
    Route::post('/', [FabricanteController::class, 'registrar']);
    Route::get('/{id}', [FabricanteController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [FabricanteController::class, 'editar'])->whereNumber('id');
    Route::delete('/{id}', [FabricanteController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Rutas para gestionar los proyectos.
 */
Route::group([ 'prefix' => 'proyectos', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [ProyectoController::class, 'listar']);
    Route::post('/', [ProyectoController::class, 'registrar']);
    Route::get('/{id}', [ProyectoController::class, 'ver'])->whereNumber('id');
    Route::put('/{id}', [ProyectoController::class, 'editar'])->whereNumber('id');
    Route::delete('/{id}', [ProyectoController::class, 'eliminar'])->whereNumber('id');
});


/**
 * Rutas para gestionar las notificaciones.
 */
Route::group([ 'prefix' => 'notificaciones', 'middleware' => ['auth:sanctum', 'habilitado'] ], function () {
    Route::get('/', [NotificacionController::class, 'listar']);
    Route::get('/chequear', [NotificacionController::class, 'chequearNuevos']);
    Route::put('/marcar', [NotificacionController::class, 'marcarLeidos']);
    Route::delete('/', [NotificacionController::class, 'eliminar'])->name('eliminar.todas');
    Route::delete('/{id}', [NotificacionController::class, 'eliminar'])->whereNumber('id');
});