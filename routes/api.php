<?php

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'Register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'Login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/logout', [\App\Http\Controllers\AuthController::class, 'Logout']);
    Route::group(['prefix' => 'clients'], function () {
        Route::get('/index', [ClientsController::class, 'Index']);
    });
    Route::group(['prefix' => 'task'], function () {
        Route::get('/index', [TaskController::class, 'index']);
    });
    Route::group(['prefix' => 'deal'], function () {
        Route::get('/index', [DealController::class, 'index']);
        Route::get('/update-status', [DealController::class, 'updateStatus']);
    });
});
