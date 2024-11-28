<?php

namespace Store\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface StoreRepository {
    public function getIngredientStock(string $ingredientName): int;
    public function updateIngredientStock(string $ingredientName, int $newStock): void;
    public function updateIngredientStocks(array $ingredients): void;
    public function getAvailableIngredients(array $ingredientNames): array;
    public function getAll(): Collection;
}
