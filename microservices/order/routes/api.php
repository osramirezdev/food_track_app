<?php

use Order\Services\Order\Impl\OrderServiceImpl;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [OrderServiceImpl::class, 'createOrder']);