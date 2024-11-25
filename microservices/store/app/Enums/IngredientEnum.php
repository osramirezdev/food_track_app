<?php

namespace Store\Enums;

enum IngredientEnum: string {
    case TOMATO = 'tomato';
    case LEMON = 'lemon';
    case POTATO = 'potato';
    case RICE = 'rice';
    case KETCHUP = 'ketchup';
    case LETTUCE = 'lettuce';
    case ONION = 'onion';
    case CHEESE = 'cheese';
    case MEAT = 'meat';
    case CHICKEN = 'chicken';

    public static function getValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
