<?php

namespace Store\Enums;

enum RecipeNameEnum: string {
    case ensalada_de_pollo = 'ensalada_de_pollo';
    case sopa_de_vegetales = 'sopa_de_vegetales';
    case papas_con_queso = 'papas_con_queso';
    case hamburguesa = 'hamburguesa';
    case ensalada_mixta = 'ensalada_mixta';
    case arroz_con_pollo = 'arroz_con_pollo';

    public static function getValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

