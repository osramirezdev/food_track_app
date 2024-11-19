<?php

namespace App\DTOs;

use App\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class KitchenDTO extends Data {
    public int $orderId;
    public string $recipeName;
    public string $status;
}
