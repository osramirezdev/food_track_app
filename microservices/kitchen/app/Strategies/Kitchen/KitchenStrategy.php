<?php

namespace Kitchen\Strategies\Kitchen;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;

interface KitchenStrategy {
    public function getType(): StoreAvailabilityEnum;
    public function apply(StoreDTO $storeDTO): void;
}
