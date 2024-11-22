<?php

namespace Kitchen\Service;

use Kitchen\DTOs\StoreDTO;

interface KitchenService {
    public function selectRandomRecipe(): StoreDTO;
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
