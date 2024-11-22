<?php

namespace Order\Services\Order;

use Order\DTOs\OrderDTO;

interface OrderService {
    public function createOrder(): OrderDTO;
    public function updateOrderRecipe(OrderDTO $dto): void;
    public function updateOrderStatus(OrderDTO $dto): void;
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
