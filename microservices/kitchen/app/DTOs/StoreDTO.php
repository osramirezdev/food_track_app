<?php

namespace Kitchen\DTOs;

use Illuminate\Support\Facades\Log;
use Kitchen\Enums\IngredientEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {
    public ?int $orderId;
    public string $recipeName;
    public array $ingredientsInStore;

    private static function mappingIngredients(array $ingredients): array {
        return array_map(function ($ingredient) {
            return [
                'ingredient' => IngredientEnum::from($ingredient->ingredient_name)->value,
                'quantity_required' => $ingredient->quantity_required,
                'current_stock' => $ingredient->current_stock ?? 0,
            ];
        }, $ingredients);
    }

    public static function fromArray(array $data): self {
        return self::from([
            'orderId' => $data['orderId'],
            'recipeName' => $data['recipeName'],
            'ingredientsInStore' => self::mappingIngredients($data['ingredientsInStore'] ?? []),
        ]);
    }

    public static function fromRecipe(int $orderId, string $recipeName, array $ingredients): self {
        return self::from([
            'orderId' => $orderId,
            'recipeName' => $recipeName,
            'ingredientsInStore' => self::mappingIngredients($ingredients),
        ]);
    }
}
