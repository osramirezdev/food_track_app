<?php

namespace Kitchen\Service;

use Kitchen\DTOs\RecipeDTO;

interface KitchenService {
    public function selectRandomRecipe(): RecipeDTO;
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
