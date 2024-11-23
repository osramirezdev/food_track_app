<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\OrderDTO;
use Kitchen\DTOs\RecipeDTO;
use Kitchen\DTOs\StoreDTO;
use Kitchen\Entities\RecipeEntity;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Mappers\RecipeMapper;
use Kitchen\Mappers\StoreDTOMapper;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Support\Facades\Log;
use Kitchen\Repository\KitchenRepository;

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
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        /**
         * FIXME
         * Find an approach in order to avoid declaration of exchanges, without depending on other microservices.
         * Exchanges are idempotent, so they are not created if they already exist
         */
        $this->provider->declareExchange('order_exchange', 'topic');
        $this->provider->declareQueueWithBindings('kitchen_queue', 'order_exchange', '*.kitchen.*');
        Log::channel('console')->debug("Configuración de RabbitMQ completada.");
    }

    public function selectRandomRecipe(): RecipeDTO {
        $recipeEntity = RecipeEntity::with('ingredients')->inRandomOrder()->first();
        $recipeDTO = RecipeMapper::entityToDTO($recipeEntity);
        return $recipeDTO;
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'kitchen_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $orderDTO = OrderDTO::from($data);
                $routingKey = $message->get('routing_key');
                if (str_starts_with($routingKey, 'order.')) {
                    $recipeDTO = $this->selectRandomRecipe();
                    $storeDTO = StoreDTOMapper::fromRecipeDTO($recipeDTO, $orderDTO->orderId);
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
