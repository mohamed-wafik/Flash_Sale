<?php

use App\Http\Controllers\api\HoldController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/products/{id}', [ProductController::class,'show']);
Route::post('/holds', [HoldController::class,'store']);
Route::post('/orders', [OrderController::class,'store']);
Route::post('/payments/webhook', [PaymentController::class,'handle']);