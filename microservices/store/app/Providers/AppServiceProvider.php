<?php

namespace Store\Providers;

use Store\Service\StoreService;

use Illuminate\Support\ServiceProvider;
use Store\Mappers\StoreDTOMapper;
use Store\Strategies\Store\Concrete\AvailableIngredientsStrategy;
use Store\Strategies\Store\Concrete\NotAvailableIngredientsStrategy;
use Store\Providers\Interfaces\IRabbitMQProvider;
use Store\Factories\RabbitMQStrategyFactory;
use Store\Factories\StoreStrategyFactory;
use Store\Mappers\IngredientMapper;
use Store\Proxy\MarketProxy;
use Store\Repositories\StoreRepository;
use Store\Repositories\Impl\StoreRepositoryImpl;
use Store\Service\Impl\StoreServiceImpl;
use Store\Strategies\RabbitMQ\Concrete\ConsumeStrategy;
use Store\Strategies\RabbitMQ\Concrete\PublishStrategy;

class AppServiceProvider extends ServiceProvider {
    
    public function register(): void {
        $this->bindServices();
        $this->bindRepositories();
        $this->bindFactories();
        $this->bindProviders();
        $this->bindMappers();
        $this->bindProxies();
    }

    private function bindServices(): void {
        $this->app->singleton(StoreService::class, StoreServiceImpl::class);
    }

    private function bindRepositories(): void {
        $this->app->singleton(StoreRepository::class, StoreRepositoryImpl::class);
    }

    private function bindProxies(): void {
        $this->app->singleton(MarketProxy::class, function () {
            return new MarketProxy();
        });
    }

    private function bindFactories(): void {
        $this->app->singleton(StoreStrategyFactory::class, function ($app) {
            return new StoreStrategyFactory([
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
        $this->app->singleton(IRabbitMQProvider::class, function ($app) {
            return new RabbitMQProvider($app->make(RabbitMQStrategyFactory::class));
        });
    }

    private function bindMappers(): void {
        $this->app->bind(StoreDTOMapper::class, function ($app): StoreDTOMapper {
            return new StoreDTOMapper();
        });

        $this->app->bind(IngredientMapper::class, function ($app): IngredientMapper {
            return new IngredientMapper();
        });
    }


    public function boot(): void {}
}
