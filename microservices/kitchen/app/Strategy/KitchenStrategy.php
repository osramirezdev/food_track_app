<?php

namespace App\Strategy;

use App\Enums\StoreAvailabilityEnum;

interface KitchenStrategy {
    public function getType(): StoreAvailabilityEnum;
    public function apply(string $dishName): string;
}
