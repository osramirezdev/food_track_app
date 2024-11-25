<?php

namespace Order\Providers;

use Order\Providers\Interfaces\IRabbitMQProvider;
use Order\Repositories\Impl\OrderRepositoryImpl;
use Order\Repositories\OrderRepository;
use Order\Services\Order\Impl\OrderServiceImpl;
use Order\Services\Order\OrderService;
use Illuminate\Support\ServiceProvider;
use Order\Factories\RabbitMQStrategyFactory;
use Order\Strategies\RabbitMQ\Concrete\ConsumeStrategy;
use Order\Strategies\RabbitMQ\Concrete\PublishStrategy;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void {
        $this->bindRepositories();
        $this->bindServices();
        $this->bindFactories();
        $this->bindProviders();
    }

    private function bindRepositories(): void {
        $this->app->singleton(OrderRepository::class, OrderRepositoryImpl::class);
    }

    private function bindServices(): void {
        $this->app->singleton(OrderService::class, OrderServiceImpl::class);
    }

    private function bindFactories(): void {
        $this->app->singleton(RabbitMQStrategyFactory::class, function () {
            return new RabbitMQStrategyFactory([
                app(PublishStrategy::class),
                app(ConsumeStrategy::class),
            ]);
        });
    }

    private function bindProviders(): void {
        $this->app->singleton(IRabbitMQProvider::class, function ($app) {
            return new RabbitMQOrderProvider($app->make(RabbitMQStrategyFactory::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
