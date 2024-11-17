<?php

namespace App\Repositories\Impl;

use App\Repositories\OrderRepository;
use App\Entities\OrderEntity;
use App\Enums\OrderStatusEnum;
use App\Enums\RecipeNameEnum;

class OrderRepositoryImpl implements OrderRepository {
    
    public function create(array $data): int {
        $order = OrderEntity::create([
            'recipe_name' => $data['recipe_name'],
            'status' => OrderStatusEnum::PENDIENTE->value,
        ]);

        return $order->id;
    }

    public function updateRecipeName(int $orderId, RecipeNameEnum $recipeName): void {
        OrderEntity::where('id', $orderId)->update(['recipe_name' => $recipeName->value]);
    }

    public function updateStatus(int $orderId, OrderStatusEnum $status): void {
        OrderEntity::where('id', $orderId)->update(['status' => $status->value]);
    }
}