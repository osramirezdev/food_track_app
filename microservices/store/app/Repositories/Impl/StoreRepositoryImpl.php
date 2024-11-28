<?php

namespace Store\Repositories\Impl;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Store\Entities\IngredientsEntity;
use Store\Mappers\IngredientMapper;
use Store\Repositories\StoreRepository;

class StoreRepositoryImpl implements StoreRepository {
    public function getIngredientStock(string $ingredientName): int {
        try{
            $ingredientEntity = IngredientsEntity::where('ingredient_name', $ingredientName)->first();
            return $ingredientEntity?->current_stock ?? 0;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateIngredientStock(string $ingredientName, int $newStock): void {
        try{
            Log::channel("console")->debug("updateIngredientStock:", [
                "ingredientName" => $ingredientName
            ]);
            $entity = IngredientsEntity::firstOrNew(['ingredient_name' => $ingredientName]);
            $entity->current_stock = $newStock;
            $entity->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateIngredientStocks(array $ingredients): void {
        try{
            $entities = IngredientMapper::dtosToEntities($ingredients);

            foreach ($entities as $entity) {
                $entity->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAvailableIngredients(array $ingredientNames): array {
        try{
            return IngredientsEntity::whereIn('ingredient_name', $ingredientNames)->get()->all();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAll(): Collection {
        try{
            return IngredientsEntity::all();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}