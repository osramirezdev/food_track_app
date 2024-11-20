<?php

namespace Kitchen\Service;

use Kitchen\DTOs\StoreDTO;

interface KitchenService {
    public function selectRandomRecipe(): string;
    public function handleStoreResponse(array $response): void;
    public function prepareDish(StoreDTO $storeDTO): void;
}
