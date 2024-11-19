<?php

namespace App\Service\Impl;

use App\DTOs\StoreDTO;
use App\Enums\RecipeNameEnum;
use App\Factories\KitchenStrategyFactory;
use App\Providers\Interfaces\IRabbitMQProvider;
use App\Repository\KitchenRepository;
use App\Service\KitchenService;

class KitchenServiceImpl implements KitchenService {
    private KitchenRepository $repository;
    private IRabbitMQProvider $messageQueueProvider;
    private KitchenStrategyFactory $strategyFactory;

    public function __construct(
        KitchenRepository $repository,
        IRabbitMQProvider $messageQueueProvider,
        KitchenStrategyFactory $strategyFactory
    ) {
        $this->repository = $repository;
        $this->messageQueueProvider = $messageQueueProvider;
        $this->strategyFactory = $strategyFactory;
    }

    public function selectRandomRecipe(): string {
        $recipes = RecipeNameEnum::getValues();
        return $recipes[array_rand($recipes)];
    }

    public function notifyOrderService(int $orderId, string $recipeName): void {
        $this->messageQueueProvider->publish(
            'order_exchange',
            'order.kitchen',
            ['orderId' => $orderId, 'recipeName' => $recipeName]
        );
    }

    public function notifyStoreService(string $recipeName): void {
        $this->messageQueueProvider->publish(
            'store_exchange',
            'store.ingredients',
            ['recipeName' => $recipeName]
        );
    }

    public function handleStoreResponse(array $response): void {}

    public function prepareDish(StoreDTO $storeDTO): void {}
}
