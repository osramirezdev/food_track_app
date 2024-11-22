<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Support\Facades\Log;
use Kitchen\Repository\KitchenRepository;
use Order\DTOs\OrderDTO;

class KitchenServiceImpl implements KitchenService {
    private KitchenRepository $repository;
    private KitchenStrategyFactory $strategyFactory;
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        KitchenRepository $repository,
        KitchenStrategyFactory $strategyFactory,
        IRabbitMQKitchenProvider $provider
    ) {
        $this->repository = $repository;
        $this->strategyFactory = $strategyFactory;
        $this->provider = $provider;
    }

    public function initializeRabbitMQ(): void {
        Log::channel('console')->debug("Init exchange and binding for RabbitMQ Kitchen");
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        $this->provider->declareQueueWithBindings('kitchen_queue', 'order_exchange', '*.kitchen.*');
        Log::channel('console')->debug("Configuración de RabbitMQ completada.");
    }

    public function selectRandomRecipe(): StoreDTO {
        $recipes = RecipeNameEnum::getValues();
        $recipeName = $recipes[array_rand($recipes)];
        $ingredients = $this->repository->getIngredientsByRecipe($recipeName);
        $storeDTO = StoreDTO::fromRecipe(
            0,
            $recipeName,
            $ingredients
        );
        return $storeDTO;
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'kitchen_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $routingKey = $message->get('routing_key');

                if (str_starts_with($routingKey, 'order.')) {
                    $storeDTO = $this->selectRandomRecipe();
                    $storeDTO->orderId = $data['orderId'];
                    $this->processOrderMessage($storeDTO);
                } elseif (str_starts_with($routingKey, 'store.')) {
                    $this->processStoreMessage($data);
                } else {
                    Log::warning("Unrecognized routing key: {$routingKey}");
                }
            },
        ]);
    }

    private function processOrderMessage(StoreDTO $storeDTO): void {
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }

    private function processStoreMessage(StoreDTO $storeDTO): void {
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }
}
