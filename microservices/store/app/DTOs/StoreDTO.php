<?php

namespace Store\DTOs;

use Illuminate\Support\Facades\Log;
use Store\Enums\StoreAvailabilityEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipeName,

        /** @var array<array{name: string, quantity_required: int, quantity_available: int}> */
        public array $ingredients,
        public ?bool $checked = false,
        public ?StoreAvailabilityEnum $availability = StoreAvailabilityEnum::NOT_AVAILABLE,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) { }

}
