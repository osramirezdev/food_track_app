<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;

class AvailableIngredientsStrategy implements KitchenStrategy {
    private IRabbitMQKitchenProvider $provider;

    // Inyectar IRabbitMQProvider para poder publicar mensajes
    public function __construct(
        IRabbitMQKitchenProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("There are no available ingredients for the dish '{$storeDTO->recipeName}', consulting store again.");

        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'order_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
                'status' => 'ESPERANDO',
            ]
        ]);
    }
}
