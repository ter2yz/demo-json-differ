<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayloadController;
use App\Http\Controllers\CompareController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Store payloads
Route::post('/payload', [PayloadController::class, 'store']);

// Compare stored payloads
Route::get('/compare', [CompareController::class, 'compare']);

// Compare custom payloads
Route::post('/compare-custom', [CompareController::class, 'compareCustom']);
