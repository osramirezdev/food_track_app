<?php

namespace Kitchen\Mappers;

use Kitchen\Entities\RecipeEntity;
use Kitchen\DTOs\RecipeDTO;
use Kitchen\DTOs\IngredientDTO;
use Kitchen\Entities\RecipeIngredientsEntity;

class RecipeMapper {
    public static function entityToDTO(RecipeEntity $entity): RecipeDTO {
        $ingredients = $entity->ingredients->map(function ($ingredient) {
            return new IngredientDTO(
                $ingredient->ingredient_name,
                $ingredient->quantity_required
            );
        })->all();

        return new RecipeDTO(
            0,
            $entity->name,
            $ingredients
        );
    }

    public static function dtoToEntity(RecipeDTO $recipeDTO): RecipeEntity {
        $recipe = new RecipeEntity(['name' => $recipeDTO->recipe]);
        $recipe->ingredients = collect($recipeDTO->ingredients)->map(function (IngredientDTO $ingredientDTO) use ($recipe) {
            return new RecipeIngredientsEntity([
                'recipe_name' => $recipe->name,
                'ingredient_name' => $ingredientDTO->ingredient_name,
                'quantity_required' => $ingredientDTO->quantity_required
            ]);
        });

        return $recipe;
    }
}
