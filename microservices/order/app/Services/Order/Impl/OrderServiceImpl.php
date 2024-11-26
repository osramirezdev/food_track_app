<?php

namespace Order\Services\Order\Impl;

use Illuminate\Support\Collection;
use Order\DTOs\OrderDTO;
use Order\Services\Order\OrderService;
use Order\Mappers\OrderMapper;
use Order\Providers\Interfaces\IRabbitMQProvider;
use Order\Repositories\OrderRepository;
use Exception;

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
        $this->provider->declareExchange('order_exchange', 'topic');
        /**
         * FIXME
         * Find an approach in order to avoid declaration of exchanges, without depending on other microservices.
         * Exchanges are idempotent, so they are not created if they already exist
         */
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        $this->provider->declareQueueWithBindings('order_queue', 'kitchen_exchange', 'order.kitchen');
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'order_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $routingKey = $message->get('routing_key');
                $orderDTO = OrderDTO::from($data);
                $this->updateOrderRecipe($orderDTO);
            },
        ]);
    }

    public function createOrder(): OrderDTO {
        $order = $this->orderRepository->create([
            'recipe_name' => null,
        ]);
        $orderDTO = OrderMapper::entityToDto($order);
        $this->publishToKitchen($orderDTO);
        return $orderDTO;
    }

    public function getOrders(): Collection {
        $orders = $this->orderRepository->getAll();
        return $orders;
    }

    public function updateOrderRecipe(OrderDTO $dto): void {
        try {
            $order = OrderMapper::dtoToEntity($dto);
            $this->orderRepository->updateRecipeName($order);
        } catch (Exception $e) {
            throw new Exception("Error updating recipe name: " . $e->getMessage());
        }
    }

    public function updateOrderStatus(OrderDTO $dto): void {
        try {
            $order = OrderMapper::dtoToEntity($dto);
            $this->orderRepository->updateStatus($order);
        } catch (Exception $e) {
            throw new Exception("Error updating status order: " . $e->getMessage());
        }
    }


    private function publishToKitchen(OrderDTO $dto): void {
        try {
            $message = json_encode($dto->toArray());
            $this->provider->executeStrategy('publish', [
                'channel' => $this->provider->getChannel(),
                'exchange' => 'order_exchange',
                'routingKey' => 'order.kitchen.*',
                'message' => $message,
            ]);
        } catch (Exception $e) {
            throw new Exception("Error publishing RabbitMQ: " . $e->getMessage());
        }
    }
}
