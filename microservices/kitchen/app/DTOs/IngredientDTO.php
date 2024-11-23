<?php

namespace Kitchen\DTOs;

use Spatie\LaravelData\Data;

class IngredientDTO extends Data {

    public function __construct(
        public string $ingredient_name,
        public int $quantity_required,
        public ?int $quantity_available = 0,
    ) { }

}
