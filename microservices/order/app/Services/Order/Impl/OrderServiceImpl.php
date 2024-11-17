<?php

namespace App\Services\Order\Impl;

use App\DTOs\OrderDTO;
use App\Repositories\Impl\OrderRepositoryImpl;
use App\Services\Order\OrderService;
use App\Enums\RecipeNameEnum;
use App\Enums\OrderStatusEnum;
use App\Factories\OrderDTOFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OrderServiceImpl implements OrderService {
    private OrderRepositoryImpl $orderRepository;

    public function __construct(
        OrderRepositoryImpl $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(): OrderDTO {
        $orderId = $this->orderRepository->create([
            'recipe_name' => null,
        ]);
        $orderDTO = OrderDTOFactory::createOrderDTO([
            'orderId' => $orderId,
            'recipeName' => null,
            'status' => OrderStatusEnum::PENDIENTE->value, 
        ]);
        return $orderDTO;
    }

    public function updateOrderRecipe(OrderDTO $dto): void {
        $this->orderRepository->updateRecipeName($dto->orderId, $dto->recipeName);
    }

    public function updateOrderStatus(OrderDTO $dto): void {
        $this->orderRepository->updateStatus($dto->orderId, $dto->status);
    }

    // aplicamos en esta clase la logica de rabbit para no romper ms arch
    public function publishOrderToQueue(OrderDTO $dto): void {
        $connection = $this->createRabbitMQConnection();
        $channel = $connection->channel();

        $this->declareExch($channel, 'order_exchange');

        $message = $this->createMessage($dto);
        $this->publishMessage($channel, $message, 'order_exchange', 'order.kitchen');

        $channel->close();
        $connection->close();
    }

    private function createRabbitMQConnection(): AMQPStreamConnection {
        $host = config('rabbitmq.host');
        $port = config('rabbitmq.port');
        $user = config('rabbitmq.username');
        $password = config('rabbitmq.password');
    
        return new AMQPStreamConnection($host, $port, $user, $password);
    }

    private function declareExch($channel, string $exchangeName): void {
        $channel->exchange_declare(
            $exchangeName,
            'topic',
            false,
            true,
            false
        );
    }

    private function createMessage(OrderDTO $dto): AMQPMessage {
        $messageBody = json_encode([
            'orderId' => $dto->orderId,
            'recipeName' => $dto->recipeName,
            'status' => $dto->status,
        ]);

        $message = new AMQPMessage($messageBody, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    
        return $message;
    }

    private function publishMessage($channel, AMQPMessage $message, string $exchangeName, string $routingKey): void {
        $channel->basic_publish($message, $exchangeName, $routingKey);
    }
}