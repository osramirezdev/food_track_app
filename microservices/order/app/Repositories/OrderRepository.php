<?php

namespace Order\Repositories;

use Illuminate\Support\Collection;
use Order\Entities\OrderEntity;

interface OrderRepository {
    public function create(array $data): OrderEntity;
    public function updateRecipeName(OrderEntity $orderEntity): void;
    public function updateStatus(OrderEntity $orderEntity): void;
    public function getAll(): Collection;
}
