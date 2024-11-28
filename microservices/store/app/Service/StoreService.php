<?php

namespace Store\Service;

use Illuminate\Database\Eloquent\Collection;
use Store\DTOs\IngredientDTO;

interface StoreService {
    public function processMessages(): void;
    public function buyIngredient(IngredientDTO $ingredient): IngredientDTO;
    public function getIngredients(): Collection;
    public function initializeRabbitMQ(): void;
}
