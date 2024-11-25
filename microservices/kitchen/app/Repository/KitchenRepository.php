<?php

namespace Kitchen\Repository;

interface KitchenRepository {
    public function getIngredientsByRecipe(string $recipeName): array;
}
