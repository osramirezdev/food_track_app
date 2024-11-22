<?php

namespace Kitchen\DTOs;

use Kitchen\Enums\IngredientEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {
    public ?int $orderId;
    public string $recipeName;
    public array $ingredientsInStore;

    public static function fromArray(array $data): self {
        return self::from([
            'orderId' => $data['orderId'],
            'recipeName' => $data['recipeName'],
            'ingredientsInStore' => array_map(function ($item) {
                return [
                    'ingredient' => IngredientEnum::from($item['ingredient']),
                    'quantity_required' => $item['quantity_required'],
                    'current_stock' => $item['current_stock'],
                ];
            }, $data['ingredientsInStore']),
        ]);
    }

    public static function fromRecipe(int $orderId, string $recipeName, array $ingredients): self {
        return self::from([
            'orderId' => $orderId,
            'recipeName' => $recipeName,
            'ingredients' => array_map(function ($ingredient) {
                return [
                    'ingredient' => IngredientEnum::from($ingredient['ingredient_name']),
                    'quantity_required' => $ingredient['quantity_required'],
                ];
            }, $ingredients),
        ]);
    }
}
