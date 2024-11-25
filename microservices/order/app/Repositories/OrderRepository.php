<?php

namespace Order\Repositories;

use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;

interface OrderRepository {
    public function create(array $data): OrderEntity;
    public function updateRecipeName(OrderEntity $orderEntity): void;
    public function updateStatus(OrderEntity $orderEntity): void;
}
