<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\OrdemServicoController;
use App\Http\Controllers\Api\ServicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - ServicoSimples
|--------------------------------------------------------------------------
|
| Rotas públicas e protegidas para o sistema de controle de OS para MEIs
|
*/

// ========== ROTAS PÚBLICAS ==========

// Dashboard público (apenas estatísticas, sem dados sensíveis)
Route::get('/dashboard', [OrdemServicoController::class, 'dashboard']);

// ========== AUTENTICAÇÃO ==========

Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

// ========== ROTAS PROTEGIDAS ==========

Route::middleware('auth:sanctum')->group(function () {

    // Usuário logado
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // CRUD completo - apenas usuários autenticados
    Route::apiResources([
        'clientes' => ClienteController::class,
        'ordem-servicos' => OrdemServicoController::class,
        'servicos' => ServicoController::class,
    ]);
});
