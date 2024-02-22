<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\UserController;
use App\Models\transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/login', [AuthController::class, 'login'])->name('login'); 

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('customer/create', [CustomerController::class, 'store']);
    Route::get('customer/all', [CustomerController::class, 'index']);
    Route::delete('customer/delete/{id}', [CustomerController::class, 'delete']);
    Route::patch('customer/update/{id}', [CustomerController::class, 'Update']);

    Route::post('account/create/{customer_id}', [AccountController::class,'store']);
    Route::delete('account/delet/{id}', [AccountController::class,'delete']);

    Route::post('account/deposit', [transactionController::class,'creditAccount']);
    Route::post('account/withdrawal', [transactionController::class,'debitAccount']);
    Route::post('account/payment', [transactionController::class, 'payment']);

    Route::get('transaction/historical', [transactionController::class, 'transactionHistorical']);
    Route::put('transaction/cancele/{id}', [transactionController::class, 'cancelTransaction']);
    
});

Route::middleware(['auth:api','admin'])->group(function () {
    Route::post('users/register', [AuthController::class, 'register'])->name('register');
    Route::get('/users/all', [UserController::class, 'index']);
});
