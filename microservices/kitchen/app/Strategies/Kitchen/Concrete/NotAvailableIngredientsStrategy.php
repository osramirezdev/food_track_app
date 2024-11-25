<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Illuminate\Support\Facades\Log;
use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
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
        $this->publishToOrder($storeDTO);
        $this->publishToStore($storeDTO);
    }

    private function publishToOrder(StoreDTO $storeDTO): void {
        Log::channel("console")->info("Publish to kitchen_exchange Order ESPERANDO.");
        $orderDTO = new OrderDTO(
            $storeDTO->orderId,
            RecipeNameEnum::from($storeDTO->recipeName),
            OrderStatusEnum::ESPERANDO,
        );
        $message = json_encode($orderDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => $message,
        ]);
    }

    private function publishToStore(StoreDTO $storeDTO): void {
        Log::channel("console")->info("Recipe not available, publish to store again");
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'store.kitchen',
            'message' => $message,
        ]);
    }
}
