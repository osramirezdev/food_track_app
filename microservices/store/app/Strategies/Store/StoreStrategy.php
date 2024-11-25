<?php

namespace Store\Strategies\Store;

use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;

interface StoreStrategy {
    public function getType(): StoreAvailabilityEnum;
    public function apply(StoreDTO $storeDTO): void;
}
