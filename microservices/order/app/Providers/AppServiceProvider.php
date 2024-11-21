<?php

namespace Order\Providers;

use Order\Providers\Interfaces\IRabbitMQProvider;
use Order\Repositories\Impl\OrderRepositoryImpl;
use Order\Repositories\OrderRepository;
use Order\Services\Order\Impl\OrderServiceImpl;
use Order\Services\Order\OrderService;
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
