<?php

namespace Kitchen\DTOs;

use Kitchen\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class KitchenDTO extends Data {
    public int $orderId;
    public string $recipeName;
    public string $status;
}
