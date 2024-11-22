<?php

namespace Order\Repositories;

use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;

interface OrderRepository {
    public function create(array $data): OrderEntity;
    public function updateRecipeName(int $orderId, RecipeNameEnum $recipeName): void;
    public function updateStatus(int $orderId, OrderStatusEnum $status): void;
}