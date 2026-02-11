<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\OrdemServicoController;
use App\Http\Controllers\Api\ServicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResources([
    'clientes' => ClienteController::class,
    'ordem-servicos' => OrdemServicoController::class,
    'servicos' => ServicoController::class,
]);

Route::get('/dashboard', [OrdemServicoController::class, 'dashboard']);
