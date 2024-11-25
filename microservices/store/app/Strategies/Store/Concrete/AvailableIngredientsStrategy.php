<?php

namespace Store\Strategies\Store\Concrete;

use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;
use Store\Strategies\Store\StoreStrategy;
use Illuminate\Support\Facades\Log;
use Store\Providers\Interfaces\IRabbitMQProvider;

class AvailableIngredientsStrategy implements StoreStrategy {
    private IRabbitMQProvider $provider;

    public function __construct(
        IRabbitMQProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::channel('console')->info("Publish to kitchen_exchange Order Available.");
        $storeDTO->availability = StoreAvailabilityEnum::AVAILABLE;
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'store_exchange',
            'routingKey' => 'store.kitchen.*',
            'message' => $message,
        ]);
    }
}
