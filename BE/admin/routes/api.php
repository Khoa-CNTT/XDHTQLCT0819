<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingoalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

Route::get('/example', [ExampleController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::get('/reset-password', [AuthController::class, 'resetShow']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::get('/get-image/{filename}', [UserController::class, 'getImage'])
    ->where('filename', '.*');


// Exception 
Route::middleware('auth:sanctum')->get('/mb-bank', [TransactionController::class, 'fetchMBBankTransactions']);

Route::middleware('auth:sanctum', 'checkRole:user,admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::get('/user/{id}', [UserController::class, 'edit']);
    Route::put('/user/income', [UserController::class, 'updateIncome']);
    Route::put('/user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/user/avatar-profile', [UserController::class, 'updateAvatarProfile']);


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
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/home', [CategoryController::class, 'showHome']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    Route::prefix('transaction')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'edit']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });

    Route::prefix('saving-goals')->group(function () {
        Route::get('/', [SavingoalController::class, 'index']);
        Route::post('/', [SavingoalController::class, 'store']);
        Route::get('/{id}', [SavingoalController::class, 'edit']);
        Route::put('/{id}', [SavingoalController::class, 'update']);
        Route::delete('/{id}', [SavingoalController::class, 'destroy']);
        Route::put('/{id}/add-money', [SavingoalController::class, 'updateSaveMoney']);
    });
    Route::prefix('reports')->group(function () {
        Route::get('/bar-chart', [ReportController::class, 'barChart']);
        Route::get('/expense-pie', [ReportController::class, 'expensePie']);
        Route::get('/income-pie', [ReportController::class, 'incomePie']);
        Route::get('/summary', [ReportController::class, 'summary']);
    });

    Route::prefix('ai')->group(function () {
        Route::post('/void', [AiController::class, 'AIVoid']);
        Route::get('/get-mbank', [AiController::class, 'fetchAndClassifyMBBankTransactions']);
    });

    /////// ---------ADMIN------------------///////
    Route::middleware(['checkRole:admin'])->group(function () {
        Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/user/update', [UserController::class, 'update']);
        Route::delete('/user/{id}', [UserController::class, 'destroy']);
        Route::put('/user/block/{id}', [UserController::class, 'block']);
    });
});
