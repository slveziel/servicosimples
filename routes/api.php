<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\OrdemServicoController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\SubscriptionController;
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

// Webhook do Asaas (público)
Route::post('/asaas/webhook', [SubscriptionController::class, 'webhook']);

// Temporary route for migrations
Route::get('/migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return response()->json(['message' => 'Migrations completed']);
});

// ========== AUTENTICAÇÃO ==========

Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

// ========== ROTAS PROTEGIDAS ==========

Route::middleware('auth:sanctum')->group(function () {

    // Usuário logado
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Assinatura (pagamentos Asaas)
    Route::prefix('subscription')->group(function () {
        Route::get('/status', [SubscriptionController::class, 'status']);
        Route::get('/details', [SubscriptionController::class, 'details']);
        Route::post('/customer', [SubscriptionController::class, 'createCustomer']);
        Route::post('/create', [SubscriptionController::class, 'createSubscription']);
        Route::get('/payment-link', [SubscriptionController::class, 'getPaymentLink']);
        Route::post('/pause', [SubscriptionController::class, 'pause']);
        Route::post('/resume', [SubscriptionController::class, 'resume']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });

    // CRUD completo - apenas usuários autenticados
    Route::apiResources([
        'clientes' => ClienteController::class,
        'ordem-servicos' => OrdemServicoController::class,
        'servicos' => ServicoController::class,
    ]);
});
