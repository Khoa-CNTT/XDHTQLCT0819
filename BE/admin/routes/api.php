<?php

use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\UserController;

Route::get('/example', [ExampleController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::get('/reset-password', [AuthController::class, 'resetShow']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('users', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'edit']);
    Route::put('/user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);


    // Account
    Route::prefix('account')->group(function () {
        // Route::post('add', [AccountController::class, 'store']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/', [AccountController::class, 'index']);
        Route::get('/{id}', [AccountController::class, 'edit']);
        // Route::put('/update', [AccountController::class, 'update']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::delete('/{id}', [AccountController::class, 'destroy']);
    });

    // Category
    Route::prefix('categories')->group(function () {
        Route::post('add', [CategoryController::class, 'store']);
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'edit']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

        // api.php
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index']);
            Route::post('/expenses', [App\Http\Controllers\ExpenseController::class, 'store']);
            Route::get('/expenses/category/{categoryId}', [App\Http\Controllers\ExpenseController::class, 'getExpensesByCategory']);
        });

    //income
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/incomes', [IncomeController::class, 'index']);
        Route::post('/incomes', [IncomeController::class, 'store']);
    });

});
