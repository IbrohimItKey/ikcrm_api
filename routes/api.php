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
use App\Http\Controllers\CouponContoller;
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
    Route::get('/price-formation', [HouseController::class, 'priceFormation']);

    Route::get('/dashboard', [ForTheBuilderController::class, 'index']);

    Route::group(['prefix' => 'clients'], function () {
        Route::get('/index', [ClientsController::class, 'Index']);
        Route::get('/all-clients', [ClientsController::class, 'allClients']);
        Route::post('/insert', [ClientsController::class, 'insert']);
        Route::post('/update', [ClientsController::class, 'update']);
        Route::get('/show', [ClientsController::class, 'show']);
        Route::post('/delete', [ClientsController::class, 'delete']);
        Route::post('/store-budget', [ClientsController::class, 'storeBudget']); // ->name('forthebuilder.clients.storeBudget');
    });
    Route::group(['prefix' => 'calendar'], function () {
        Route::get('/index', [ClientsController::class, 'calendar']);
        Route::post('/store-task', [TaskController::class, 'task_store']);
        Route::get('/get-user', [ClientsController::class, 'getUsers']);
        Route::get('/get-deal', [ClientsController::class, 'getDeals']);
    });
    Route::group(['prefix' => 'task'], function () {
        Route::get('/index', [TaskController::class, 'index']);
    });
    Route::group(['prefix' => 'user'], function () {
        Route::get('/index', [UserController::class, 'user_index'])->name('user.index');
        Route::post('/insert', [UserController::class, 'user_store'])->name('user.store');
        Route::get('/edit/{id}', [UserController::class, 'user_edit'])->name('user.edit');
        Route::post('/update', [UserController::class, 'user_update'])->name('user.update');
        Route::get('/show', [UserController::class, 'user_show'])->name('user.show');
        Route::post('/delete', [UserController::class, 'user_destroy'])->name('user.destroy');
    });
    Route::group(['prefix' => 'installment-plan'], function () {
        Route::get('/index', [InstallmentPlanController::class, 'plan_index']);
        Route::get('/show', [InstallmentPlanController::class, 'plan_show']);
        Route::post('/pay-sum', [InstallmentPlanController::class, 'plan_paySum']);
        Route::post('/remove-payment', [InstallmentPlanController::class, 'plan_reduceSum']);
    });
    Route::group(['prefix' => 'deal'], function () {
        Route::get('/index', [DealController::class, 'deal_index']);
        Route::post('/update-status', [DealController::class, 'deal_updateStatus']);
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
        Route::get('/edit', [LanguageController::class, 'languageEdit']);

        Route::match(['get', 'post'], '/create', [LanguageController::class, 'store']);

        Route::match(['get', 'post'],'/create',[LanguageController::class, 'store']);
        Route::get('/edit', [LanguageController::class, 'languageEdit']);
        Route::get('/innershow', [LanguageController::class, 'innershow']);
        Route::post('/translation/save', [LanguageController::class, 'translation_save']);

        Route::post('/update', [LanguageController::class, 'update']);
        Route::post('/delete', [LanguageController::class, 'languageDestroy']);
        // Route::post('/booking_period/update', [BookingController::class, 'bookingPeriodUpdate']);
    });
    Route::group(['prefix' => 'coupon'], function () {
        Route::get('/index', [CouponContoller::class, 'index']);
        // Route::post('/insert', [BookingController::class, 'store']);
        // Route::post('update', [BookingController::class, 'statusUpdate']);
        // Route::post('/delete', [LanguageController::class, 'languageDestroy']);

    });
    Route::group(['prefix' => 'booking'], function () {
        Route::get('/index', [BookingController::class, 'index']);
        Route::get('/show', [BookingController::class, 'show']);
        Route::post('/insert', [BookingController::class, 'store']);
        Route::post('/show/status/update', [BookingController::class, 'statusUpdate']);
        Route::post('/booking_period/update', [BookingController::class, 'bookingPeriodUpdate']);

    });

});