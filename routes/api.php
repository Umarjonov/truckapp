<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyAdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NotcameController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserListController;
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
    Route::post('delete', [AuthController::class, 'delete'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'location'], function () {
    Route::post('submit', [TrackerController::class, 'register'])->middleware('auth:sanctum');
    Route::post('/user-tracks/{user_id}', [TrackerController::class, 'getUserTracksByUserId'])->middleware('auth:sanctum');
    Route::post('last-submit', [TrackerController::class, 'lastSubmit'])->middleware('auth:sanctum');
    Route::post('user/not-come', [NotcameController::class, 'notCame'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'password'], function () {
    Route::post('/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/verify', [AuthController::class, 'verifyCode']);
    Route::post('/reset', [AuthController::class, 'resetPassword']);

});

Route::group(['prefix' => 'admin'], function () {
    Route::get('user/info', [AdminController::class, 'info'])->middleware('auth:sanctum');
    Route::post('user/data/{user_id}', [TrackerController::class, 'getUserIdTracks'])->middleware('auth:sanctum');
    Route::post('user/data', [TrackerController::class, 'getUserTracks'])->middleware('auth:sanctum');
    Route::post('user/info/track', [CompanyController::class, 'getUserInfoAndTruckInfo'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'moderator'], function () {
    Route::post('truck/update/{truck_id}', [TrackerController::class, 'updateTruckData'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'company'], function () {
    Route::post('company-add', [CompanyController::class, 'createCompany'])->middleware('auth:sanctum');
    Route::post('add/company-admin', [CompanyAdminController::class, 'addCompanyAdmin'])->middleware('auth:sanctum');
    Route::post('companies/users', [CompanyController::class, 'viewCompanyUsers'])->middleware('auth:sanctum');
    Route::post('companies/{companyId}/status-change', [CompanyController::class, 'changeCompanyStatus'])->middleware('auth:sanctum');
    Route::post('companies/company_list', [CompanyController::class, 'companyList'])->middleware('auth:sanctum');
    Route::post('companies/admins/{companyId}', [CompanyController::class, 'getCompanyAdmins'])->middleware('auth:sanctum');
    Route::post('companies/hr/list', [CompanyController::class, 'getCompanyHrs'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'company-admin'], function () {
    Route::post('admin-add/manager', [EmployeeController::class, 'adminAddHrOrManager'])->middleware('auth:sanctum');
    Route::post('manager-add/user', [EmployeeController::class, 'createAdminToUser'])->middleware('auth:sanctum');
    Route::post('hrs-delete/{userId}', [EmployeeController::class, 'deleteHr'])->middleware('auth:sanctum');
    Route::post('users-delete/{userId}', [EmployeeController::class, 'deleteUser'])->middleware('auth:sanctum');
    Route::post('admins-delete/{userId}', [EmployeeController::class, 'deleteAll'])->middleware('auth:sanctum');
    Route::post('users/{id}/position', [UserListController::class, 'makePosition'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'position'], function () {
    Route::post('create-position', [PositionController::class, 'createPosition'])->middleware('auth:sanctum');
    Route::post('get-positions', [PositionController::class, 'getAllPositions'])->middleware('auth:sanctum');
});
Route::group(['prefix' => 'rank'], function () {
    Route::post('create-rank', [RankController::class, 'createRank'])->middleware('auth:sanctum');
    Route::post('get-ranks', [RankController::class, 'getAllRanks'])->middleware('auth:sanctum');
});




