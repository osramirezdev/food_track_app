<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;
use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\DTOs\OrderDTO;

class NotAvailableIngredientsStrategy implements KitchenStrategy {
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        IRabbitMQKitchenProvider $provider
    ) {
        $this->provider = $provider;
    }


    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("Publish to kitchen_exchange Order Not Available.");
        $this->publishToOrder($storeDTO);
        Log::info("Theres not available ingredients. Recipe: '{$storeDTO->recipeName}', consulting to store again.");
    }

    private function publishToOrder(StoreDTO $storeDTO): void {
        $orderDTO = new OrderDTO(
            $storeDTO->orderId,
            RecipeNameEnum::from($storeDTO->recipeName),
            OrderStatusEnum::ESPERANDO,
        );
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                $orderDTO
            ],
        ]);
    }

    private function publishToStore(StoreDTO $storeDTO): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'store.kitchen',
            'message' => [
                $storeDTO
            ],
        ]);
    }
}
