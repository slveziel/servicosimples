<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page (home)
Route::get('/', function () {
    return view('landing');
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
