<?php

use App\Http\Controllers\AdminController;
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
});


Route::post('submit', [TrackerController::class, 'register'])->middleware('auth:sanctum');
Route::get('last-submit', [TrackerController::class, 'lastsubmit'])->middleware('auth:sanctum');
Route::get('history-submit', [TrackerController::class, 'getDataBetweenDates'])->middleware('auth:sanctum');


Route::group(['prefix' => 'password'], function () {
    Route::post('/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/verify', [AuthController::class, 'verifyCode']);
    Route::post('/reset', [AuthController::class, 'resetPassword']);

});

Route::group(['prefix' => 'admin'], function () {
    Route::get('user/info', [AdminController::class, 'info'])->middleware('auth:sanctum');
    Route::post('user/data/{user_id}', [TrackerController::class, 'getUserIdTracks'])->middleware('auth:sanctum');
    Route::post('user/info/track', [TrackerController::class, 'userTruckDaily'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'moderator'], function () {
    Route::post('truck/update/{truck_id}', [TrackerController::class, 'updateTruckData'])->middleware('auth:sanctum');
    Route::post('truck/add-user', [AuthController::class, 'addModerator'])->middleware('auth:sanctum');
});










