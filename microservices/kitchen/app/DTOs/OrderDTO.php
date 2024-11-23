<?php

namespace Kitchen\DTOs;

use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class OrderDTO extends Data {

    public function __construct(
        public ?int $orderId = null,
        public ?RecipeNameEnum $recipeName = null,
        public ?OrderStatusEnum $status = OrderStatusEnum::PENDIENTE,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) { }

}
