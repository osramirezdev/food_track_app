<?php

namespace App\Strategy\Concrete;

use App\DTOs\StoreDTO;
use App\Enums\StoreAvailabilityEnum;
use App\Strategy\KitchenStrategy;

class AvailableIngredientsStrategy implements KitchenStrategy {
    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(string $dishName): string {
        return "Theres available ingredients for dish '{$dishName}'.";
    }
}
