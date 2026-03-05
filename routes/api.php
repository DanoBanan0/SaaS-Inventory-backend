<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('units', [UnitController::class, 'index']);
    Route::get('units/{unit}', [UnitController::class, 'show']);
    Route::get('employees', [EmployeeController::class, 'index']);
    Route::get('employees/{employee}', [EmployeeController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('purchases', [PurchaseController::class, 'index']);
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show']);
    Route::get('devices', [DeviceController::class, 'index']);
    Route::get('devices/{device}', [DeviceController::class, 'show']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('/audits', [AuditController::class, 'index']);
    Route::get('/audits/export', [AuditController::class, 'exportAll']);

    Route::middleware('role')->group(function () {
        Route::post('units', [UnitController::class, 'store']);
        Route::put('units/{unit}', [UnitController::class, 'update']);
        Route::delete('units/{unit}', [UnitController::class, 'destroy']);

        Route::post('employees', [EmployeeController::class, 'store']);
        Route::put('employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy']);

        Route::post('categories', [CategoryController::class, 'store']);
        Route::post('categories/{category}/fields', [CategoryController::class, 'addField']);

        Route::post('purchases', [PurchaseController::class, 'store']);
        Route::put('purchases/{purchase}', [PurchaseController::class, 'update']);
        Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy']);

        Route::post('devices', [DeviceController::class, 'store']);
        Route::put('devices/{device}', [DeviceController::class, 'update']);

        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);

        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);

        Route::delete('/audits', [AuditController::class, 'deleteAll']);
    });
});
