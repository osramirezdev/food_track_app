<?php

namespace Order\DTOs;

use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class OrderDTO extends Data {
    public ?int $orderId = null;
    public ?RecipeNameEnum $recipeName = null;
    public OrderStatusEnum $status = OrderStatusEnum::PENDIENTE;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
