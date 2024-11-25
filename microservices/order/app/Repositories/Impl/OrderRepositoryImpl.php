<?php

namespace Order\Repositories\Impl;

use Illuminate\Support\Collection;
use Order\Repositories\OrderRepository;
use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderRepositoryImpl implements OrderRepository {

    public function create(array $data): OrderEntity {
        $order = OrderEntity::create([
            'recipe_name' => $data['recipe_name'],
            'status' => OrderStatusEnum::PENDIENTE->value,
        ]);

        return $order;
    }

    public function updateRecipeName(OrderEntity $order): void {
        DB::beginTransaction();
        try {
            $updated = OrderEntity::where('id', $order->id)->update([
                'recipe_name' => $order->recipe_name,
                'status' => $order->status ? OrderStatusEnum::from($order->status)->value : null,
            ]);

            if ($updated === 0) {
                throw new \Exception("Error updating recipe. Order: {$order->id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('console')->debug("Error updating ", ["data" => $e]);
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(OrderEntity $order): void {
        DB::beginTransaction();
        try {
            $updated = OrderEntity::where('id', $order->id)->update([
                'recipe_name' => $order->recipe_name,
                'status' => $order->status ? OrderStatusEnum::from($order->status)->value : null,
            ]);

            if ($updated === 0) {
                throw new \Exception("Error updating status. Order: {$order->id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::channel('console')->debug("Error updating ", ["data" => $e]);
            DB::rollBack();
            throw $e;
        }
    }

    public function getAll(): Collection {
        try{
            return OrderEntity::all();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
