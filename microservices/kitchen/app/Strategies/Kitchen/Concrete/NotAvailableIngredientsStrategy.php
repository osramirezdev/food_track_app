<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;

class NotAvailableIngredientsStrategy implements KitchenStrategy {
    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("Theres not available ingredients for dish '{$storeDTO->recipeName}', consulting to store again.");
    }
}
