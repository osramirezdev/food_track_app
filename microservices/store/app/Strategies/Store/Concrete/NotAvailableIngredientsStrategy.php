<?php

namespace Store\Strategies\Store\Concrete;

use Illuminate\Support\Facades\Log;
use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;
use Store\Strategies\Store\StoreStrategy;
use Store\Providers\Interfaces\IRabbitMQProvider;

class NotAvailableIngredientsStrategy implements StoreStrategy {
    private IRabbitMQProvider $provider;

    public function __construct(
        IRabbitMQProvider $provider
    ) {
        $this->provider = $provider;
    }


    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        $this->publishToKitchen($storeDTO);
    }

    private function publishToKitchen(StoreDTO $storeDTO): void {
        Log::channel('console')->info("Publish to kitchen_exchange Order Not Available.");
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'store_exchange',
            'routingKey' => 'store.kitchen.*',
            'message' => $message,
        ]);
    }
}
