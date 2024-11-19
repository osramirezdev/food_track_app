<?php

namespace App\Providers;

use App\Providers\Interfaces\IRabbitMQProvider;
use App\Repositories\Impl\OrderRepositoryImpl;
use App\Repositories\OrderRepository;
use App\Services\Order\Impl\OrderServiceImpl;
use App\Services\Order\OrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->singleton(OrderRepository::class, OrderRepositoryImpl::class);
        $this->app->singleton(OrderService::class, OrderServiceImpl::class);
        $this->app->singleton(IRabbitMQProvider::class, RabbitMQProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
