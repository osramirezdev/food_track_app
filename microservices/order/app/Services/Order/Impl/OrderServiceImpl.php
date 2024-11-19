<?php

namespace App\Services\Order\Impl;

use App\DTOs\OrderDTO;
use App\Services\Order\OrderService;
use App\Mappers\OrderMapper;
use App\Providers\Interfaces\IRabbitMQProvider;
use App\Repositories\OrderRepository;
use Exception;

class OrderServiceImpl implements OrderService {
    private OrderRepository $orderRepository;

    public function __construct(
        OrderRepository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(): OrderDTO {
        $order = $this->orderRepository->create([
            'recipe_name' => null,
        ]);
        $orderDTO = OrderMapper::entityToDto($order);
        $this->publishOrderToQueue($orderDTO);
        return $orderDTO;
    }

    public function updateOrderRecipe(OrderDTO $dto): void {
        try {
            $this->orderRepository->updateRecipeName($dto->orderId, $dto->recipeName);
        } catch (Exception $e) {
            throw new Exception("Error updating recipe name: " . $e->getMessage());
        }
    }

    public function updateOrderStatus(OrderDTO $dto): void {
        try {
            $this->orderRepository->updateStatus($dto->orderId, $dto->status);
        } catch (Exception $e) {
            throw new Exception("Error updating status order: " . $e->getMessage());
        }
    }

    private function publishOrderToQueue(OrderDTO $dto): void {
        try {
            $rabbitMQProvider = app(IRabbitMQProvider::class);
            $rabbitMQProvider->publish(
                'order_exchange',
                'order.kitchen',
                [
                    'orderId' => $dto->orderId,
                    'recipeName' => $dto->recipeName,
                    'status' => $dto->status,
                ]
            );
        } catch (Exception $e) {
            throw new Exception("Error publishing RabbitMQ: " . $e->getMessage());
        }
    }
}