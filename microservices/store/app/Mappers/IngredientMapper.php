<?php

namespace Store\Mappers;

use Store\Entities\IngredientsEntity;
use Store\DTOs\IngredientDTO;

class IngredientMapper {

    public static function entityToDto(IngredientsEntity $entity, ?int $required = 0): IngredientDTO {
        $ingredientDTO = new IngredientDTO(
            ingredient_name: $entity->ingredient_name,
            quantity_required: $required,
            quantity_available: $entity->current_stock ?? 0
        );

        return $ingredientDTO;
    }

    public static function dtoToEntity(IngredientDTO $dto): IngredientsEntity {
        $ingredientEntity = IngredientsEntity::firstOrNew(['ingredient_name' => $dto->ingredient_name]);
        $ingredientEntity->current_stock = $dto->quantity_available;
        return $ingredientEntity;
    }

    public static function entitiesToDtos($ingredientsEntities): array {
        $dtos = collect($ingredientsEntities)
        ->map(fn(IngredientsEntity $entity) => self::entityToDto($entity))
        ->all();
        return $dtos;
    }

    public static function dtosToEntities(array $dtos): array {
        $entities = collect($dtos)
            ->map(fn(IngredientDTO $dto) => self::dtoToEntity($dto))
            ->values()
            ->all();
        
        return $entities;
    }

    public static function mapWithRequired(array $ingredientsRequired, array $ingredientsEntities): array {
        $ingredientMap = collect($ingredientsEntities)
            ->keyBy(fn(IngredientsEntity $ingredientEntity) => $ingredientEntity->ingredient_name);
    
        return array_map(function ($ingredientRequired) use ($ingredientMap) {
            $ingredientName = $ingredientRequired['ingredient_name'];
            $quantityRequired = $ingredientRequired['quantity_required'];
    
            $ingredientEntity = $ingredientMap->get($ingredientName, new IngredientsEntity([
                'ingredient_name' => $ingredientName,
                'current_stock' => 0,
            ]));
    
            return self::entityToDto($ingredientEntity, $quantityRequired);
        }, $ingredientsRequired);
    }
}
