<?php

namespace Kitchen\Repository\Impl;

use Illuminate\Support\Facades\DB;
use Kitchen\Repository\KitchenRepository;

class KitchenRepositoryImpl implements KitchenRepository {
    public function getIngredientsByRecipe(string $recipeName): array {
        try {
            return DB::table('kitchen.recipe_ingredients')
                ->where('recipe_name', $recipeName)
                ->select('ingredient_name', 'quantity_required')
                ->get()
                ->map(function ($ingredient) {
                    return [
                        'ingredient_name' => $ingredient->ingredient_name,
                        'quantity_required' => $ingredient->quantity_required,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            throw $e;
        };
    }
}