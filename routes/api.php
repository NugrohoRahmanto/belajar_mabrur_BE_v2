<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api.key')->get('/test', function () {
    return response()->json([
        'message' => 'API Key Accepted'
    ]);
});

Route::middleware('api.key')->group(function () {
    Route::get('/content', [\App\Http\Controllers\API\ContentController::class, 'index']);
    Route::get('/content/category', [\App\Http\Controllers\API\ContentController::class, 'byCategory']);


    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/current',   [AuthController::class, 'current']);
    Route::post('/logout',   [AuthController::class, 'logout']);

    Route::get('/user/me', [UserController::class, 'me']);
    Route::put('/user/update', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);

    // Only admin
    Route::get('/user/all', [UserController::class, 'allUsers']);
});

