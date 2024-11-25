<?php

namespace Kitchen\DTOs;

use Spatie\LaravelData\Data;

class RecipeDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipe,
        /** @var array<array{name: string, quantity_available: int}> */
        public array $ingredients,
    ) { }

}
