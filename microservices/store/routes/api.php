<?php

use Store\Service\StoreService;
use Illuminate\Support\Facades\Route;

Route::post('/store', [StoreService::class, 'createOrder']);