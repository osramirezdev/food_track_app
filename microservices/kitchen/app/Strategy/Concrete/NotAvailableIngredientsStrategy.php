<?php

namespace App\Strategy\Concrete;

use App\Enums\StoreAvailabilityEnum;
use App\Strategy\KitchenStrategy;

class NotAvailableIngredientsStrategy implements KitchenStrategy {
    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(string $dishName): string {
        return "Theres not available ingredients for dish '{$dishName}', consulting to store again.";
    }
}
