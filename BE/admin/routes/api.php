<?php

use App\Http\Controllers\AccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

Route::get('/example', [ExampleController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::get('/reset-password', [AuthController::class, 'resetShow']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
// Test API

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
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/', [AccountController::class, 'index']);
        Route::get('/{id}', [AccountController::class, 'edit']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::put('/set-primary-account/{id}', [AccountController::class, 'setPrimaryAccount']);
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
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/add', [ExpenseController::class, 'store']);
        Route::get('/expenses/category/{categoryId}', [ExpenseController::class, 'getExpensesByCategory']);
    });

    Route::prefix('transaction')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/add', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'edit']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
        Route::get('/mbbank', [TransactionController::class, 'fetchMBBankTransactions']);
    });


    //income
    Route::prefix('incomes')->group(function () {
        Route::get('/incomes', [IncomeController::class, 'index']);
        Route::post('/incomes', [IncomeController::class, 'store']);
    });
});
