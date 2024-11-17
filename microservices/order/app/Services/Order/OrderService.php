<?php

namespace App\Services\Order;

use App\DTOs\OrderDTO;

interface OrderService {
    public function createOrder(): OrderDTO;
    public function updateOrderRecipe(OrderDTO $dto): void;
    public function updateOrderStatus(OrderDTO $dto): void;
    public function publishOrderToQueue(OrderDTO $dto): void;
}