<?php

namespace App\DTOs;

use App\Enums\OrderStatusEnum;
use App\Enums\RecipeNameEnum;
use Spatie\DataTransferObject\DataTransferObject;

class OrderDTO extends DataTransferObject {
    public ?int $orderId = null;
    public ?RecipeNameEnum $recipeName = null;
    public OrderStatusEnum $status = OrderStatusEnum::PENDIENTE; // por default ponemos pendiente
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __construct(array $parameters = []) {
        $parameters["status"] = $parameters["status"] ?? OrderStatusEnum::PENDIENTE->value;
        parent::__construct($parameters);
    }
}
