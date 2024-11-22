<?php

namespace Order\Services\Order\Impl;

use Order\DTOs\OrderDTO;
use Order\Services\Order\OrderService;
use Order\Mappers\OrderMapper;
use Order\Providers\Interfaces\IRabbitMQProvider;
use Order\Repositories\OrderRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderServiceImpl implements OrderService {
    private OrderRepository $orderRepository;
    private IRabbitMQProvider $provider;

    public function __construct(
        OrderRepository $orderRepository,
        IRabbitMQProvider $provider
    ) {
        $this->orderRepository = $orderRepository;
        $this->provider = $provider;
    }

    public function initializeRabbitMQ(): void {
        Log::channel('console')->debug("Init Rabbit Order");
        $this->provider->declareExchange('order_exchange', 'topic');
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
            $this->provider->publish(
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
