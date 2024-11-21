<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Support\Facades\Log;

class KitchenServiceImpl implements KitchenService {
    private KitchenStrategyFactory $strategyFactory;
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        KitchenStrategyFactory $strategyFactory,
        IRabbitMQKitchenProvider $provider
    ) {
        $this->strategyFactory = $strategyFactory;
        $this->provider = $provider;
    }

    public function initializeRabbitMQ(): void {
        Log::debug("inicializando rabbit para kitchen");
        $this->provider->declareExchange('kitchen_exchange', 'topic');
    }

    public function selectRandomRecipe(): string {
        $recipes = RecipeNameEnum::getValues();
        return $recipes[array_rand($recipes)];
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'queue' => 'kitchen_exchange',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $routingKey = $message->get('routing_key');

                if (str_starts_with($routingKey, 'order.')) {
                    $this->processOrderMessage($data);
                } elseif (str_starts_with($routingKey, 'store.')) {
                    $this->processStoreMessage($data);
                } else {
                    Log::warning("Unrecognized routing key: {$routingKey}");
                }
            },
        ]);
    }

    private function processOrderMessage(array $data): void {
        $recipeName = $this->selectRandomRecipe();

        $this->publish('store_exchange', 'store.kitchen', [
            'orderId' => $data['orderId'],
            'recipeName' => $recipeName,
        ]);

        $this->publish('order_exchange', 'order.kitchen', [
            'orderId' => $data['orderId'],
            'recipeName' => $recipeName,
            'status' => 'PROCESANDO',
        ]);

        Log::info("Processed order: {$data['orderId']} with recipe: $recipeName");
    }

    private function processStoreMessage(array $data): void {
        $storeDTO = StoreDTO::fromArray($data);
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);

        if ($storeDTO->ingredientsInStore) {
            $this->publish('order_exchange', 'order.kitchen', [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
                'status' => 'LISTO',
            ]);
            Log::info("Order: {$storeDTO->orderId} ready!");
        } else {
            Log::info("Awaiting ingredients for order: {$storeDTO->orderId}");
        }
    }

    private function publish(string $exchange, string $routingKey, array $message): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => $exchange,
            'routingKey' => $routingKey,
            'message' => $message,
        ]);
    }
}
