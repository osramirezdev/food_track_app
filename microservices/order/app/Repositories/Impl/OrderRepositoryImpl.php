<?php

namespace Order\Repositories\Impl;

use Order\Repositories\OrderRepository;
use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
use Illuminate\Support\Facades\DB;

class OrderRepositoryImpl implements OrderRepository {

    public function create(array $data): OrderEntity {
        $order = OrderEntity::create([
            'recipe_name' => $data['recipe_name'],
            'status' => OrderStatusEnum::PENDIENTE->value,
        ]);

        return $order;
    }

    public function updateRecipeName(int $orderId, RecipeNameEnum $recipeName): void {
        DB::beginTransaction();
        try {
            $updated = OrderEntity::where('id', $orderId)->update(['recipe_name' => $recipeName->value, 'status' => OrderStatusEnum::PROCESANDO]);

            if ($updated === 0) {
                throw new \Exception("Error updating recipe. Order: {$orderId}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $orderId, OrderStatusEnum $status): void {
        DB::beginTransaction();
        try {
            $updated = OrderEntity::where('id', $orderId)->update(['status' => $status->value]);

            if ($updated === 0) {
                throw new \Exception("Error updating status. Order: {$orderId}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
