<?php

namespace Store\Service;

use Store\DTOs\IngredientDTO;

interface StoreService {
    public function processMessages(): void;
    public function buyIngredient(IngredientDTO $ingredient): IngredientDTO;
    public function initializeRabbitMQ(): void;
}
