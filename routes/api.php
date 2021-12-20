<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GearController;
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
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});
// Gear controller routes
Route::group(['prefix' => 'gear'], function () {
    Route::get('all', [GearController::class, 'index']);
    Route::get('', [GearController::class, 'userIndex']);
    Route::get('{id}', [GearController::class, 'userShow']);
    Route::get('all/{id}', [GearController::class, 'show']);
    Route::post('create', [GearController::class, 'store']);
    Route::put('update/{id}',  [GearController::class, 'update']);
    Route::put('lend/{id}',  [GearController::class, 'lend']);
    Route::delete('delete/{id}',  [GearController::class, 'destroy']);
});
// Company controller routes
Route::group(['prefix' => 'companies'], function () {
    Route::get('', [CompanyController::class, 'index']);
    Route::get('{id}', [CompanyController::class, 'show']);
    Route::post('create', [CompanyController::class, 'store']);
    Route::put('update/{id}',  [CompanyController::class, 'update']);
    Route::delete('delete/{id}',  [CompanyController::class, 'destroy']);
});
// Giveaway request controller routes
Route::group(['prefix' => 'requests'], function () {
    Route::get('', [RequestController::class, 'index']);
    Route::get('{id}', [RequestController::class, 'show']);
    Route::post('create', [RequestController::class, 'store']);
    Route::put('update/{id}',  [RequestController::class, 'update']);
    Route::delete('delete/{id}',  [RequestController::class, 'destroy']);
});
// User controller routes
Route::group(['prefix' => 'users'], function () {
    Route::get('', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('update/{id}',  [UserController::class, 'update']);
    Route::delete('delete/{id}',  [UserController::class, 'destroy']);
});
