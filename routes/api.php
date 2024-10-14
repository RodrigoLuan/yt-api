<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Video\VideoController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth.jwt');
Route::get('me', [AuthController::class, 'me'])->middleware('auth.jwt');

Route::middleware('auth')->group(function () {
    Route::post('/videos', [VideoController::class, 'store']);
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);
});

Route::get('/test-jwt', function() {
    return response()->json(['message' => 'JWT auth working']);
})->middleware('auth.jwt');
