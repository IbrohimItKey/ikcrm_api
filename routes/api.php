<?php

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\InstallmentPlanController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\AuthController;
use App\Models\House;
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

Route::post('/register', [AuthController::class, 'Register']);
Route::post('/login', [AuthController::class, 'Login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'Logout']);
    Route::get('/house', [HouseController::class, 'index']);
    Route::group(['prefix' => 'clients'], function () {
        Route::get('/index', [ClientsController::class, 'Index']);
        Route::get('/show', [ClientsController::class, 'show'])->name('clients.show');
    });
    Route::get('/calendar', [ClientsController::class, 'calendar']);
    Route::group(['prefix' => 'task'], function () {
        Route::get('/index', [TaskController::class, 'index']);
    });
    Route::group(['prefix' => 'installment-plan'], function () {
        Route::get('/index', [InstallmentPlanController::class, 'index']);
    });
    Route::group(['prefix' => 'deal'], function () {
        Route::get('/index', [DealController::class, 'index']);
        Route::get('/update-status', [DealController::class, 'updateStatus']);
    });
});
