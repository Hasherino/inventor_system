<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GearController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\RequestController;
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
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
// Gear controller routes
Route::group(['prefix' => 'gear'], function () {
    Route::get('all', [GearController::class, 'index']);
    Route::get('', [GearController::class, 'userIndex']);
    Route::get('user/{id}', [GearController::class, 'selectedIndex']);
    Route::get('code/{code}', [GearController::class, 'showByCode']);
    Route::get('all/{id}', [GearController::class, 'show']);
    Route::get('pdf/{id}', [GearController::class, 'generatePDF']);
    Route::get('{id}', [GearController::class, 'userShow']);
    Route::post('', [GearController::class, 'store']);
    Route::put('{id}',  [GearController::class, 'update']);
    Route::delete('{id}',  [GearController::class, 'destroy']);
});
// Company controller routes
Route::group(['prefix' => 'companies'], function () {
    Route::get('', [CompanyController::class, 'index']);
    Route::get('{id}', [CompanyController::class, 'show']);
    Route::post('', [CompanyController::class, 'store']);
    Route::put('{id}',  [CompanyController::class, 'update']);
    Route::delete('{id}',  [CompanyController::class, 'destroy']);
});
// Giveaway request controller routes
Route::group(['prefix' => 'requests'], function () {
    Route::get('', [RequestController::class, 'index']);
    Route::get('pending', [RequestController::class, 'pendingRequests']);
    Route::get('{id}', [RequestController::class, 'show']);
    Route::post('lend/{id}',  [RequestController::class, 'lend']);
    Route::post('acceptLend/{id}', [RequestController::class, 'acceptLend']);
    Route::post('return/{id}', [RequestController::class, 'returnLend']);
    Route::post('acceptReturn/{id}', [RequestController::class, 'acceptReturnLend']);
    Route::post('giveaway/{id}', [RequestController::class, 'giveaway']);
    Route::post('acceptGiveaway/{id}', [RequestController::class, 'acceptGiveaway']);
    Route::post('giveYourself/{id}', [RequestController::class, 'giveawayToYourself']);
    Route::post('declineReturn/{id}', [RequestController::class, 'declineReturnLend']);
    Route::delete('{id}',  [RequestController::class, 'destroy']);
});
// User controller routes
Route::group(['prefix' => 'users'], function () {
    Route::get('all', [UserController::class, 'index']);
    Route::get('', [UserController::class, 'userIndex']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('', [UserController::class, 'register']);
    Route::put('{id}',  [UserController::class, 'update']);
    Route::delete('{id}',  [UserController::class, 'destroy']);
});
// Password reset routes
Route::post('reset-password', [PasswordResetRequestController::class, 'sendPasswordResetEmail']);
Route::post('change-password', [ChangePasswordController::class, 'passwordResetProcess']);
// History routes
Route::get('history', [HistoryController::class, 'index']);
Route::get('gearHistory/{id}', [HistoryController::class, 'gearIndex']);
