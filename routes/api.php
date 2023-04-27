<?php

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\InstallmentPlanController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForTheBuilderController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\UserController;
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

    Route::get('/dashboard', [ForTheBuilderController::class, 'index']);

    Route::group(['prefix' => 'clients'], function () {
        Route::get('/index', [ClientsController::class, 'Index']);
        Route::get('/all-clients', [ClientsController::class, 'allClients']);
        Route::post('/insert', [ClientsController::class, 'insert']);
        Route::get('/show', [ClientsController::class, 'show'])->name('clients.show');
    });
    Route::get('/calendar/index', [ClientsController::class, 'calendar']);
    Route::group(['prefix' => 'task'], function () {
        Route::get('/index', [TaskController::class, 'index']);
    });
    Route::group(['prefix' => 'user'], function () {
        Route::get('/index', [UserController::class, 'index'])->name('user.index');
        Route::post('/insert', [UserController::class, 'store'])->name('user.store');
        Route::put('/update', [UserController::class, 'update'])->name('user.update');
        Route::get('/show', [UserController::class, 'show'])->name('user.show');
        Route::delete('/destroy', [UserController::class, 'destroy'])->name('user.destroy');
    });
    Route::group(['prefix' => 'installment-plan'], function () {
        Route::get('/index', [InstallmentPlanController::class, 'index']);
        Route::get('/show', [InstallmentPlanController::class, 'show']);
        Route::post('/pay-sum', [InstallmentPlanController::class, 'paySum']);
        Route::post('/remove-payment', [InstallmentPlanController::class, 'reduceSum']);
    });
    Route::group(['prefix' => 'deal'], function () {
        Route::get('/index', [DealController::class, 'index']);
        Route::post('/update-status', [DealController::class, 'updateStatus']);
    });
    Route::group(['prefix' => 'booking'], function () {
        Route::get('/index', [BookingController::class, 'index']);
        Route::get('/show', [BookingController::class, 'show']);
        Route::post('/insert', [BookingController::class, 'store']);
        Route::post('/show/status/update', [BookingController::class, 'statusUpdate']);
        Route::post('/booking_period/update', [BookingController::class, 'bookingPeriodUpdate']);

    });
    Route::group(['prefix' => 'language'], function () {
        Route::get('/index', [LanguageController::class, 'index']);
        Route::match(['get', 'post'],'/create',[LanguageController::class, 'store']);
        Route::get('/edit', [LanguageController::class, 'languageEdit']);
        Route::get('/innershow', [LanguageController::class, 'innershow']);
        Route::post('/translation/save', [LanguageController::class, 'translation_save']);
        Route::post('/update', [LanguageController::class, 'update']);
        Route::post('/delete', [LanguageController::class, 'languageDestroy']);
        // Route::post('/booking_period/update', [BookingController::class, 'bookingPeriodUpdate']);
    });
    Route::group(['prefix' => 'currency'], function () {
        Route::get('/index', [CurrencyController::class, 'index']);
        Route::post('/update', [CurrencyController::class, 'update']);
        // Route::get('/show', [BookingController::class, 'show']);
        // Route::post('/insert', [BookingController::class, 'store']);
        // Route::post('/show/status/update', [BookingController::class, 'statusUpdate']);

    });

}); 
