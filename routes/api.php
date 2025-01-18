<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SavingBalance;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\WithdrawController;
use App\Models\Income;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::get('/balance', [BalanceController::class, 'index']);
    Route::post('/balance', [BalanceController::class, 'store']);
    Route::post('/wish', [WishController::class, 'store']);
    Route::get('/wish', [WishController::class, 'index']);
    Route::put('/wish/{id}', [WishController::class, 'update']);
    Route::delete('/wish/{id}', [WishController::class, 'destroy']);
    Route::get('/wish/detail/{id}', [WishController::class, 'show']);
    Route::get('/saving/{id}', [SavingBalance::class, 'index']);
    Route::post('/saving/wish/{id}', [SavingBalance::class, 'store']);
    Route::post('/withdraw/wish/{id}', [WithdrawController::class, 'store']);
    Route::get('/withdraw/{id}', [WithdrawController::class, 'index']);
    Route::get('/history', [UserController::class, 'index']);
    Route::get('/incomes', [IncomeController::class, 'index']);
    Route::post('/incomes', [IncomeController::class, 'store']);
});

