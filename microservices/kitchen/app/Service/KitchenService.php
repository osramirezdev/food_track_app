<?php

namespace App\Service;

use App\DTOs\StoreDTO;

interface KitchenService {
    public function selectRandomRecipe(): string;
    public function notifyOrderService(int $orderId, string $recipeName): void;
    public function notifyStoreService(string $recipeName): void;
    public function handleStoreResponse(array $response): void;
    public function prepareDish(StoreDTO $storeDTO): void;
}
