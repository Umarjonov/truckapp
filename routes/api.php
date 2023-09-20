<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackerController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'create']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('user/info', [AuthController::class, 'info'])->middleware('auth:sanctum');

});

Route::post('submit', [TrackerController::class, 'register'])->middleware('auth:sanctum');

Route::prefix('password')->group(function () {
    Route::post('/forgot', [AuthController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/verify', [AuthController::class, 'verifyCode'])->name('password.verify');
    Route::post('/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
});
