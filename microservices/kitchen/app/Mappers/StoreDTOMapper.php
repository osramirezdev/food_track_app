<?php

namespace Kitchen\Mappers;

use kepka42\LaravelMapper\Mapper\AbstractMapper;
use Kitchen\DTOs\IngredientDTO;
use Kitchen\DTOs\RecipeDTO;
use Kitchen\DTOs\StoreDTO;
use Illuminate\Support\Facades\Log;
use Kitchen\Entities\RecipeEntity;

class StoreDTOMapper extends AbstractMapper
{
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
        Log::channel('console')->debug("FromRecipe params", ["recipeDTO"=>$recipeDTO, "orderId"=>$orderId]);
        foreach ($recipeDTO->ingredients as $k => $v) {
            Log::channel("console")->debug("cada receta", ["key"=>$k,"value"=>$v]);
        }

        $ingredients = collect($recipeDTO->ingredients)
        ->map(function (IngredientDTO $ingredient) {
            Log::channel("console")->debug("nombre ingrediente", ["ingredient_name"=>$ingredient->ingredient_name]);
            Log::channel("console")->debug("quantity_required", ["quantity_required"=>$ingredient->quantity_required]);
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
