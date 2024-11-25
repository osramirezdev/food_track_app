<?php

namespace Kitchen\DTOs;

use Illuminate\Support\Facades\Log;
use Kitchen\Enums\IngredientEnum;
use Kitchen\Enums\StoreAvailabilityEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipeName,

        /** @var array<array{name: string, quantity_required: int, quantity_available: int}> */
        public array $ingredients,
        public ?bool $checked = false,
        public ?StoreAvailabilityEnum $availability = StoreAvailabilityEnum::NOT_AVAILABLE, // default

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) { }

    public function hasSufficientStock(): bool {
        Log::channel("console")->info("ingredientes ahora: ", [""=>$this->ingredients]);
        return collect($this->ingredients)
            ->every(fn($ingredient) => $ingredient->quantity_available > 0);
    }
}
