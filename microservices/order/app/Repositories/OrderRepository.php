<?php

namespace App\Repositories;
use App\Enums\OrderStatusEnum;
use App\Enums\RecipeNameEnum;

interface OrderRepository {
    public function create(array $data): int;
    public function updateRecipeName(int $orderId, RecipeNameEnum $recipeName): void;
    public function updateStatus(int $orderId, OrderStatusEnum $status): void;
}