# Proyecto de Microservicio Order

## Descripción general

Este microservicio se encarga de crear un pedido, publicar a Microservicio Kitchen el pedido y consumir respuesta de Microservicio Kitchen el estado de la misma. 

## Estructura del Proyecto y directorio actual

### Path: `/home/oramirez/Documents/repositorios/alegra/prueba_tecnica_alegra_oscar_ramirez/microservices/order/`
```bash
app/
├── Console
│   ├── Commands
│   │   └── ConsumeOrderMessages.php
│   └── Kernel.php
├── DTOs
│   └── OrderDTO.php
├── Entities
│   └── OrderEntity.php
├── Enums
│   ├── OrderStatusEnum.php
│   └── RecipeNameEnum.php
├── Events
│   └── OrderUpdated.php
├── Factories
│   ├── OrderDTOFactory.php
│   └── RabbitMQStrategyFactory.php
├── Http
│   └── Controllers
│       ├── Controller.php
│       └── OrderController.php
├── Mappers
│   └── OrderMapper.php
├── Models
│   └── User.php
├── Providers
│   ├── AppServiceProvider.php
│   ├── Interfaces
│   │   └── IRabbitMQProvider.php
│   └── RabbitMQOrderProvider.php
├── Repositories
│   ├── Impl
│   │   └── OrderRepositoryImpl.php
│   └── OrderRepository.php
├── Services
│   └── Order
│       ├── Impl
│       │   └── OrderServiceImpl.php
│       └── OrderService.php
└── Strategies
    └── RabbitMQ
        ├── Concrete
        │   ├── ConsumeStrategy.php
        │   └── PublishStrategy.php
        └── RabbitMQStrategy.php
```
## Descripción de Archivos y Funcionalidades

## `ConsumeOrderMessages.php`
Este archivo deberia tener la logica de inicializacion de rabbitmq, y de invocar al consumer y producer en su servicio.
```php
<?php

namespace Order\Console\Commands;

use Order\Services\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeOrderMessages extends Command {

    protected $signature = 'rabbitmq:consume-orders';
    protected $description = 'Command description';

    private OrderService $orderService;


    public function __construct(
        OrderService $orderService
    ) {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function handle() {
        Log::channel('console')->info("Init Order consumer");
        $this->orderService->initializeRabbitMQ();
        $this->orderService->processMessages();
    }
}
```
## `RecipeNameEnum.php`
Enums para recetas.
```php
<?php

namespace Order\Enums;

enum RecipeNameEnum: string {
    case ensalada_de_pollo = 'ensalada_de_pollo';
    case sopa_de_vegetales = 'sopa_de_vegetales';
    case papas_con_queso = 'papas_con_queso';
    case hamburguesa = 'hamburguesa';
    case ensalada_mixta = 'ensalada_mixta';
    case arroz_con_pollo = 'arroz_con_pollo';

    public static function getValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
```
## `OrderStatusEnum.php`
Enums para estados de pedidos.
```php
<?php

namespace Order\Enums;

enum OrderStatusEnum: string {
    case PENDIENTE = 'PENDIENTE';
    case ESPERANDO = 'ESPERANDO';
    case PROCESANDO = 'PROCESANDO';
    case LISTO = 'LISTO';
}
```
## `OrderDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Store.
```php
<?php

namespace Order\DTOs;

use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
use Spatie\LaravelData\Data;

class OrderDTO extends Data {
    public function __construct(
        public ?int $orderId = null,
        public ?RecipeNameEnum $recipeName = null,
        public ?OrderStatusEnum $status = OrderStatusEnum::PENDIENTE,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) { }

}
```
## `RabbitMQStrategyFactory.php`
Este archivo contiene el patron de fabrica para determinar cual sera la estrategia a implementar para el proveedor de rabbitmq.
```php
<?php

namespace Order\Factories;

use Order\Strategies\RabbitMQ\RabbitMQStrategy;
use InvalidArgumentException;

class RabbitMQStrategyFactory {
    private array $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = [];
        foreach ($strategies as $strategy) {
            if ($strategy instanceof RabbitMQStrategy) {
                $this->strategies[$strategy->getType()] = $strategy;
            }
        }
    }

    public function getStrategy(string $type): RabbitMQStrategy {
        if (!isset($this->strategies[$type])) {
            throw new InvalidArgumentException("No strategy found for type: {$type}");
        }
        return $this->strategies[$type];
    }
}
```
## `IRabbitMQProvider.php`
Este archivo contiene los metodos para el provider de rabbitmq
```php
<?php

namespace Order\Providers\Interfaces;

use PhpAmqpLib\Channel\AMQPChannel;

interface IRabbitMQProvider {
    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void;
    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void;
    public function executeStrategy(string $type, array $params): void;
    public function getChannel();
}
```
## `RabbitMQOrderProvider.php`
Archivo con la implementacion de meatodos para provider RabbitMQ.
```php
<?php

namespace Order\Providers;

use Order\Factories\RabbitMQStrategyFactory;
use Order\Providers\Interfaces\IRabbitMQProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQOrderProvider implements IRabbitMQProvider{
    private ?AMQPStreamConnection $connection = null;
    private RabbitMQStrategyFactory $strategyFactory;

    public function __construct(
        RabbitMQStrategyFactory $strategyFactory
    ) {
        $this->strategyFactory = $strategyFactory;
    }

    private function validateConfiguration(): void {
        $requiredKeys = ['host', 'port', 'username', 'password', 'queue'];
        foreach ($requiredKeys as $key) {
            if (empty(config("rabbitmq.$key"))) {
                throw new Exception("RabbitMQ '$key' not configured.");
            }
            Log::channel('console')->info("RabbitMQ '$key' defined.");
        }
    }

    private function connect(): void {
        if ($this->connection === null || !$this->connection->isConnected()) {
            try {
                $this->validateConfiguration();
                $this->connection = new AMQPStreamConnection(
                    config('rabbitmq.host'),
                    config('rabbitmq.port'),
                    config('rabbitmq.username'),
                    config('rabbitmq.password')
                );
            } catch (Exception $e) {
                logger()->error("Error connecting RabbitMQ: {$e->getMessage()}");
                Log::error("Error initializing rabbit ", ["error" => $e->getMessage()]);
                throw $e;
            }
        }
    }

    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void {
        $this->connect();
        $channel = $this->getChannel();
        Log::debug("Declaring Exchange. Channel: ", ["channel" => $channel]);
        $channel->exchange_declare(
            $exchangeName,
            $type,
            false,
            $durable,
            false
        );
    }

    public function executeStrategy(string $type, array $params): void {
        $this->connect();
        $channel = $this->connection->channel();

        $strategy = $this->strategyFactory->getStrategy($type);
        Log::debug("Se ha obtenido la estregia ", ["strategy" => $strategy]);

        $strategy->execute(array_merge(['channel' => $channel], $params));

        $channel->close();
    }

    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void {
        $this->connect();
        $channel = $this->getChannel();

        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchangeName, $routingKey);

        Log::info("Queue '{$queueName}' bound to exchange '{$exchangeName}' with routing key '{$routingKey}'");
    }

    public function getChannel(): AMQPChannel {
        $this->connect();
        return $this->connection->channel();
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
```
## `OrderRepository.php`
Este archivo contiene los metodos para consultas a base de datos.
```php
<?php

namespace Order\Repositories;

use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;

interface OrderRepository {
    public function create(array $data): OrderEntity;
    public function updateRecipeName(OrderEntity $orderEntity): void;
    public function updateStatus(OrderEntity $orderEntity): void;
}
```
## `OrderRepositoryImpl.php`
Este archivo contiene los metodos para consultas a base de datos.
```php
<?php

namespace Order\Repositories\Impl;

use Order\Repositories\OrderRepository;
use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
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
}
```
## `OrderService.php`
Este archivo contiene el contrato de estrategias para la logica de Ordern.
```php
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
```
## `OrderServiceImpl.php`
Este archivo contiene toda la logica de manejo de mensajes.
```php
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
```
## `RabbitMQStrategy.php`
Este archivo contiene el contrato de las estrategias que seran implementadas.
```php
<?php

namespace Order\Strategies\RabbitMQ;

interface RabbitMQStrategy {
    public function getType(): string;
    public function execute(array $params): void;
}
```
## `ConsumeStrategy.php`
Este archivo contiene la estrategia de consumo de mensajes.
```php
<?php

namespace Order\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Order\Strategies\RabbitMQ\RabbitMQStrategy;
use Psr\Log\LoggerInterface;

class ConsumeStrategy implements RabbitMQStrategy {

    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function getType(): string {
        return 'consume';
    }

    public function execute(array $params): void {
        Log::channel('console')->info('Init spatie log'. json_encode($params));

        $params['channel']->basic_consume(
            $params['queue'],
            '',
            false,
            true,
            false,
            false,
            $params['callback']
        );

        $this->logger->info('Consumidor configurado, esperando mensajes.');

        while ($params['channel']->is_consuming()) {
            $params['channel']->wait();
        }
    }
}
```
## `PublishStrategy.php`
Este archivo contiene la informacion para publicar mensajes
```php
<?php

namespace Order\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Order\Strategies\RabbitMQ\RabbitMQStrategy;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class PublishStrategy implements RabbitMQStrategy {

    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function getType(): string {
        return 'publish';
    }

    public function execute(array $params): void {
        Log::channel('console')->debug("Publishing to kitchen ", ["data" => $params]);
        $message = new AMQPMessage(
            json_encode($params['message']),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );
        $this->logger->info('Publishing message: ' . json_encode($params['message']));
        Log::channel('console')->info('Testing spatie log'. json_encode($params['message']));

        $params['channel']->basic_publish(
            $message,
            $params['exchange'],
            $params['routingKey']
        );
        Log::channel('console')->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey'] . $params['message']);
        $this->logger->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);

    }
}
```
## `OrderUpdated.php`
```php
<?php

namespace Order\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;
use Order\DTOs\OrderDTO;

class OrderUpdated implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $orderId;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $orderId, $status) {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {
        Log::channel("console")->info('Broadcasting event on channel: orders');
        return [
            new Channel('order.' . $this->orderId)
        ];
    }

    public function broadcastAs(): string {
        return 'OrderUpdate';
    }

    public function broadcastWith() {
        return new OrderDTO(
            $this->order->id,
            $this->order->recipe_name,
            $this->order->status,
        );
    }

}
```
## `channels.php`
```php
<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('OrderUpdate', function ($user) {
    return true;
});

```
