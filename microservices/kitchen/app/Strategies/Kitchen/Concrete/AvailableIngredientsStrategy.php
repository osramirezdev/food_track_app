<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;
use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;

class AvailableIngredientsStrategy implements KitchenStrategy {
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        IRabbitMQKitchenProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
                'status' => OrderStatusEnum::PROCESANDO,
            ],
        ]);
        sleep(10);
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
                'status' => OrderStatusEnum::LISTO,
            ],
        ]);

        Log::info("Recipe '{$storeDTO->recipeName}' for order ID: {$storeDTO->orderId}. Ready!");
    }
}
