<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GearController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});
// Gear controller routes
Route::group(['prefix' => 'gear'], function () {
    Route::group(['middleware' => 'admin'], function () {
        Route::get('all', [GearController::class, 'index']);
        Route::delete('{id}', [GearController::class, 'destroy']);
        Route::get('user/{id}', [GearController::class, 'selectedIndex']);
        Route::get('all/{id}', [GearController::class, 'show']);
    });
    Route::get('', [GearController::class, 'userIndex']);
    Route::get('code/{code}', [GearController::class, 'showByCode']);
    Route::get('pdf/{id}', [GearController::class, 'generatePDF']);
    Route::get('{id}', [GearController::class, 'userShow']);
    Route::post('', [GearController::class, 'store']);
    Route::put('{id}',  [GearController::class, 'update']);
});
// Company controller routes
Route::group(['prefix' => 'companies', 'middleware' => 'admin'], function () {
    Route::get('', [CompanyController::class, 'index']);
    Route::post('', [CompanyController::class, 'store']);
    Route::put('{id}',  [CompanyController::class, 'update']);
    Route::delete('{id}',  [CompanyController::class, 'destroy']);
});
// Giveaway request controller routes
Route::group(['prefix' => 'requests'], function () {
    Route::get('pending', [RequestController::class, 'pendingRequests']);
    Route::post('lend',  [RequestController::class, 'lend']);
    Route::post('accept-lend/{id}', [RequestController::class, 'acceptLend']);
    Route::post('return', [RequestController::class, 'returnLend']);
    Route::post('accept-return/{id}', [RequestController::class, 'acceptReturnLend']);
    Route::post('giveaway', [RequestController::class, 'giveaway']);
    Route::post('accept-giveaway/{id}', [RequestController::class, 'acceptGiveaway']);
    Route::post('give-yourself', [RequestController::class, 'giveawayToYourself'])->middleware('admin');
    Route::post('decline-return/{id}', [RequestController::class, 'declineReturnLend']);
    Route::delete('{id}',  [RequestController::class, 'destroy']);
});
// User controller routes
Route::group(['prefix' => 'users'], function () {
    Route::group(['middleware' => 'admin'], function () {
        Route::get('all', [UserController::class, 'index']);
        Route::post('', [UserController::class, 'register']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });
    Route::get('', [UserController::class, 'userIndex']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}',  [UserController::class, 'update']);
});
// Password reset routes
Route::post('reset-password', [PasswordResetRequestController::class, 'sendPasswordResetEmail']);
Route::post('new-password', [ResetPasswordController::class, 'passwordResetProcess']);
Route::post('change-password', [ChangePasswordController::class, 'changePassword']);
// History routes
Route::get('history', [HistoryController::class, 'index']);
Route::get('gear-history/{id}', [HistoryController::class, 'gearIndex']);
