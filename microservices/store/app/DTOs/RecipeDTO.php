<?php

namespace Store\DTOs;

use Spatie\LaravelData\Data;

class RecipeDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipe,
        /** @var array<array{ingredient_name: string, quantity_available: int}> */
        public array $ingredients,
    ) { }

}
