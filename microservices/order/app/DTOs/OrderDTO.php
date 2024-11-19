<?php

namespace App\DTOs;

use App\Enums\OrderStatusEnum;
use App\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class OrderDTO extends Data {
    public ?int $orderId = null;
    public ?RecipeNameEnum $recipeName = null;
    public OrderStatusEnum $status = OrderStatusEnum::PENDIENTE;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
