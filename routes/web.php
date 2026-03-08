<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page - serve static PWA site
Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// App (authenticated area) - serves the AngularJS app
Route::get('/app', function () {
    return view('welcome');
});

// Fallback for SPA-like behavior in app
Route::get('/app/{any}', function () {
    return view('welcome');
})->where('any', '.*');

require __DIR__.'/api.php';
