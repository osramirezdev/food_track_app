<?php

namespace Kitchen\Mappers;

use kepka42\LaravelMapper\Mapper\AbstractMapper;
use Kitchen\DTOs\IngredientDTO;
use Kitchen\DTOs\RecipeDTO;
use Kitchen\DTOs\StoreDTO;

class StoreDTOMapper extends AbstractMapper {
    protected $sourceType = "";

    protected $hintType = StoreDTO::class;

    public function map($object, $params = []): StoreDTO {
        return StoreDTO::from(
            $data['orderId'] ?? null,
            $data['recipe'] ?? 'unknown',
            $data['ingredients'] ?? []
        );
    }

    public static function fromRecipeDTO(RecipeDTO $recipeDTO, ?int $orderId = null): StoreDTO {
        $ingredients = collect($recipeDTO->ingredients)
        ->map(function (IngredientDTO $ingredient) {
            return new IngredientDTO(
                $ingredient->ingredient_name,
                $ingredient->quantity_required,
            );
        })
        ->all();

        return new StoreDTO(
            $orderId,
            $recipeDTO->recipe,
            $ingredients
        );
    }

}
