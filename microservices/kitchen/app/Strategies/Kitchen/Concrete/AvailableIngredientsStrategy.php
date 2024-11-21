<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;

class AvailableIngredientsStrategy implements KitchenStrategy {
    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("Ingredients are available. Recipe '{$storeDTO->recipeName}'.");
        Log::info("Recipe '{$storeDTO->recipeName}' for order ID: {$storeDTO->orderId}. Ready!");
    }
}
