<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SiteConfigController;
use App\Http\Controllers\Api\FidelizacionController;
use App\Http\Controllers\Api\ClienteController;

// Autenticación
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);

    // Rutas protegidas - productos
    Route::get('/products/all', [ProductController::class, 'allProducts']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Rutas protegidas - categorías (crear / actualizar / eliminar)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->group(function () {
    // Gestión de clientes (Admin)
    Route::get('/admin/clientes', [ClienteController::class, 'index']);
    Route::get('/admin/clientes/{id}', [ClienteController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/admin/clientes/{id}/sellos', [ClienteController::class, 'updateSellos'])->where('id', '[0-9]+');
});

// Rutas públicas
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Rutas de promociones
Route::apiResource('promos', PromoController::class);

// Rutas del dashboard
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Rutas de pedidos
Route::post('/orders', [OrderController::class, 'store']);

// Rutas de configuración del sitio
Route::get('site-config', [SiteConfigController::class, 'index']); // PÚBLICO para que cargue

// Rutas de fidelización
Route::get('/clientes/{telefono}', [FidelizacionController::class, 'show'])->where('telefono', '[a-zA-Z0-9\-]+');
Route::post('/clientes/pedido', [FidelizacionController::class, 'registrarPedido']);
Route::post('/clientes/reclamar', [FidelizacionController::class, 'reclamarRecompensa']);

// Solo POST requiere autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::post('site-config', [SiteConfigController::class, 'store']);
});