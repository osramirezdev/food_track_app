<?php

use Order\Services\Order\OrderService;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderService::class, 'createOrder']);