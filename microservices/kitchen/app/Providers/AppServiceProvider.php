<?php

namespace Kitchen\Providers;

use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Factories\RabbitMQStrategyFactory;
use Kitchen\Mappers\StoreDTOMapper;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Repository\KitchenRepository;
use Kitchen\Repository\Impl\KitchenRepositoryImpl;
use Kitchen\Service\Impl\KitchenServiceImpl;
use Kitchen\Service\KitchenService;
use Kitchen\Strategies\RabbitMQ\Concrete\PublishStrategy;
use Kitchen\Strategies\Kitchen\Concrete\AvailableIngredientsStrategy;
use Kitchen\Strategies\Kitchen\Concrete\NotAvailableIngredientsStrategy;
use Kitchen\Strategies\RabbitMQ\Concrete\ConsumeStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->bindRepositories();
        $this->bindServices();
        $this->bindFactories();
        $this->bindProviders();
        $this->bindMappers();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void{}

    private function bindRepositories(): void {
        $this->app->singleton(KitchenRepository::class, KitchenRepositoryImpl::class);
    }

    private function bindServices(): void {
        $this->app->singleton(KitchenService::class, KitchenServiceImpl::class);
    }

    private function bindFactories(): void {
        $this->app->singleton(KitchenStrategyFactory::class, function ($app) {
            return new KitchenStrategyFactory([
                $app->make(AvailableIngredientsStrategy::class),
                $app->make(NotAvailableIngredientsStrategy::class),
            ]);
        });

        $this->app->singleton(RabbitMQStrategyFactory::class, function () {
            return new RabbitMQStrategyFactory([
                app(PublishStrategy::class),
                app(ConsumeStrategy::class),
            ]);
        });
    }

    private function bindProviders(): void {
        $this->app->singleton(IRabbitMQKitchenProvider::class, function ($app) {
            return new RabbitMQKitchenProvider($app->make(RabbitMQStrategyFactory::class));
        });
    }

    private function bindMappers(): void {
        $this->app->bind(StoreDTOMapper::class, function ($app): StoreDTOMapper {
            return new StoreDTOMapper();
        });
    }
}
