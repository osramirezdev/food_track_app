<?php

namespace Kitchen\Service;

use Kitchen\DTOs\StoreDTO;

interface KitchenService {
    public function selectRandomRecipe(): string;
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
