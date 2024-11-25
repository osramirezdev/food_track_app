<?php

namespace Store\Repositories;

interface StoreRepository {
    public function getIngredientStock(string $ingredientName): int;
    public function updateIngredientStock(string $ingredientName, int $newStock): void;
    public function updateIngredientStocks(array $ingredients): void;
    public function getAvailableIngredients(array $ingredientNames): array;
}
