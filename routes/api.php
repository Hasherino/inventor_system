<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GearController;
use App\Http\Controllers\RequestController;
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
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
// Gear controller routes
Route::group(['prefix' => 'gear'], function () {
    Route::get('', [GearController::class, 'index']);
    Route::get('{id}', [GearController::class, 'show']);
    Route::post('create', [GearController::class, 'store']);
    Route::put('update/{id}',  [GearController::class, 'update']);
    Route::delete('delete/{id}',  [GearController::class, 'destroy']);
});
// Company controller routes
Route::group(['prefix' => 'company'], function () {
    Route::get('', [CompanyController::class, 'index']);
    Route::get('{id}', [CompanyController::class, 'show']);
    Route::post('create', [CompanyController::class, 'store']);
    Route::put('update/{id}',  [CompanyController::class, 'update']);
    Route::delete('delete/{id}',  [CompanyController::class, 'destroy']);
});
// Giveaway request controller routes
Route::group(['prefix' => 'request'], function () {
    Route::get('', [RequestController::class, 'index']);
    Route::get('{id}', [RequestController::class, 'show']);
    Route::post('create', [RequestController::class, 'store']);
    Route::put('update/{id}',  [RequestController::class, 'update']);
    Route::delete('delete/{id}',  [RequestController::class, 'destroy']);
});
