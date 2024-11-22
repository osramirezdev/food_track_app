# Proyecto de Microservicio Kitchen

## Descripción general

Este microservicio se encarga de recibir un pedido, elegir una receta, publicar a Microservicio Order y Microservicio Store sobre la receta, y consumir respuesta de store, si hay ingredientes suficientes pasa el pedido listo a Microservicio Order. 

## Estructura del Proyecto

## Estructura del directorio actual 

### Path: `/home/oramirez/Documents/repositorios/alegra/prueba_tecnica_alegra_oscar_ramirez/microservices/kitchen/`
```bash
.
├── Console
│   ├── Commands
│   │   └── ConsumeKitchenMessages.php
│   └── Kernel.php
├── DTOs
│   ├── OrderDTO.php
│   └── StoreDTO.php
├── Enums
│   ├── IngredientEnum.php
│   ├── OrderStatusEnum.php
│   ├── RecipeNameEnum.php
│   └── StoreAvailabilityEnum.php
├── Factories
│   ├── KitchenStrategyFactory.php
│   └── RabbitMQStrategyFactory.php
├── Http
├── Models
├── Providers
│   ├── AppServiceProvider.php
│   ├── Interfaces
│   │   └── IRabbitMQKitchenProvider.php
│   └── RabbitMQKitchenProvider.php
├── Repository
│   ├── Impl
│   │   └── KitchenRepositoryImpl.php
│   └── KitchenRepository.php
├── Service
│   ├── Impl
│   │   └── KitchenServiceImpl.php
│   └── KitchenService.php
└── Strategies
    ├── Kitchen
    │   ├── Concrete
    │   │   ├── AvailableIngredientsStrategy.php
    │   │   └── NotAvailableIngredientsStrategy.php
    │   └── KitchenStrategy.php
    └── RabbitMQ
        ├── Concrete
        │   ├── ConsumeStrategy.php
        │   └── PublishStrategy.php
        └── RabbitMQStrategy.php
```
## Descripción de Archivos y Funcionalidades

## `ConsumeKitchenMessages.php`
Este archivo deberia tener la logica de inicializacion de rabbitmq, y de invocar al consumer y producer en su servicio.
```php
<?php

namespace Kitchen\Console\Commands;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Service\KitchenService;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeKitchenMessages extends Command {
    protected $signature = 'rabbitmq:consume-kitchen';
    protected $description = 'Consume messages for kitchen service.';

    private KitchenService $kitchenService;

    public function __construct(KitchenService $kitchenService) {
        parent::__construct();
        $this->kitchenService = $kitchenService;
    }

    public function handle(): void {
        Log::channel('console')->info("Init kitchen consumer");
        $this->kitchenService->initializeRabbitMQ();
        $this->kitchenService->processMessages();
    }
}
```
## `IngredientEnum.php`
Enums para ingredientes.
```php
<?php

namespace Kitchen\Enums;

enum IngredientEnum: string {
    case TOMATO = 'tomato';
    case LEMON = 'lemon';
    case POTATO = 'potato';
    case RICE = 'rice';
    case KETCHUP = 'ketchup';
    case LETTUCE = 'lettuce';
    case ONION = 'onion';
    case CHEESE = 'cheese';
    case MEAT = 'meat';
    case CHICKEN = 'chicken';

    public static function getValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
```
## `OrderStatusEnum.php`
Enums para ingredientes.
```php
<?php

namespace Kitchen\Enums;

enum OrderStatusEnum: string {
    case PENDIENTE = 'PENDIENTE';
    case ESPERANDO = 'ESPERANDO';
    case PROCESANDO = 'PROCESANDO';
    case LISTO = 'LISTO';
}
```
## `RecipeNameEnum.php`
Enums para ingredientes.
```php
<?php

namespace Kitchen\Enums;

enum RecipeNameEnum: string {
    case ensalada_de_pollo = 'ensalada_de_pollo';
    case sopa_de_vegetales = 'sopa_de_vegetales';
    case papas_con_queso = 'papas_con_queso';
    case hamburguesa = 'hamburguesa';
    case ensalada_mixta = 'ensalada_mixta';

    public static function getValues(): array {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
```
## `StoreAvailabilityEnum.php`
Enums para manejar disponibilidades de ingredientes.
```php
<?php

namespace Kitchen\Enums;

enum StoreAvailabilityEnum: string {
    case AVAILABLE = 'Available Ingredients';
    case NOT_AVAILABLE = 'Not Available Ingredients';
}
```
## `OrderDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Store.
```php
<?php

namespace Kitchen\DTOs;

use Spatie\LaravelData\Data;

class OrderDTO extends Data {
    public ?int $orderId;
    public string $recipeName;
    public string $status;
}
```
## `StoreDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Store.
```php
<?php

namespace Kitchen\DTOs;

use Kitchen\Enums\IngredientEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {
    public ?int $orderId;
    public string $recipeName;
    public array $ingredientsInStore;

    public static function fromArray(array $data): self {
        return self::from([
            'orderId' => $data['orderId'],
            'recipeName' => $data['recipeName'],
            'ingredientsInStore' => array_map(function ($item) {
                return [
                    'ingredient' => IngredientEnum::from($item['ingredient']),
                    'quantity_required' => $item['quantity_required'],
                    'current_stock' => $item['current_stock'],
                ];
            }, $data['ingredientsInStore']),
        ]);
    }

    public static function fromRecipe(int $orderId, string $recipeName, array $ingredients): self {
        return self::from([
            'orderId' => $orderId,
            'recipeName' => $recipeName,
            'ingredients' => array_map(function ($ingredient) {
                return [
                    'ingredient' => IngredientEnum::from($ingredient['ingredient_name']),
                    'quantity_required' => $ingredient['quantity_required'],
                ];
            }, $ingredients),
        ]);
    }
}
```
## `RabbitMQStrategyFactory.php`
Este archivo contiene el patron de fabrica para determinar cual sera la estrategia a implementar para el proveedor de rabbitmq.
```php
<?php

namespace Kitchen\Factories;

use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
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
## `IRabbitMQKitchenProvider.php`
Este archivo contiene los metodos para el provider de rabbitmq
```php
<?php

namespace Kitchen\Providers\Interfaces;

interface IRabbitMQKitchenProvider {
    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void;
    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void;
    public function executeStrategy(string $type, array $params): void;
    public function getChannel();
}
```
## `RabbitMQKitchenProvider.php`
Archivo con la implementacion de metodos para provider RabbitMQ.
```php
<?php

namespace Kitchen\Providers;

use Exception;
use Illuminate\Support\Facades\Log;
use Kitchen\Factories\RabbitMQStrategyFactory;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQKitchenProvider implements IRabbitMQKitchenProvider {
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
                throw new Exception("RabbitMQ '$key' not defined.");
            }
            Log::channel('console')->info("RabbitMQ '$key' defined.");
        }
    }

    private function connect(): void {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->validateConfiguration();
            $this->connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.username'),
                config('rabbitmq.password')
            );
        }
    }

    public function executeStrategy(string $type, array $params): void {
        $this->connect();
        $channel = $this->connection->channel();

        $strategy = $this->strategyFactory->getStrategy($type);
        Log::debug("Se ha obtenido la estregia ", ["strategy" => $strategy]);

        $strategy->execute(array_merge(['channel' => $channel], $params));

        $channel->close();
    }

    public function getChannel(): AMQPChannel {
        $this->connect();
        return $this->connection->channel();
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

    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void {
        $this->connect();
        $channel = $this->getChannel();

        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchangeName, $routingKey);

        Log::info("Queue '{$queueName}' bound to exchange '{$exchangeName}' with routing key '{$routingKey}'");
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
```
## `KitchenRepository.php`
Este archivo contiene los metodos para consultas a base de datos.
```php
<?php

namespace Kitchen\Repository;

interface KitchenRepository {
    public function getIngredientsByRecipe(string $recipeName): array;
}
```
## `KitchenRepositoryImpl.php`
Este archivo contiene los metodos para consultas a base de datos.
```php
<?php

namespace Kitchen\Repository\Impl;

use Illuminate\Support\Facades\DB;
use Kitchen\Repository\KitchenRepository;

class KitchenRepositoryImpl implements KitchenRepository {
    public function getIngredientsByRecipe(string $recipeName): array {
        try {
            return DB::table('kitchen.recipe_ingredients')
                ->where('recipe_name', $recipeName)
                ->select('ingredient_name', 'quantity_required')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            throw $e;
        };
    }
}
```
## `KitchenService.php`
Este archivo contiene el contrato de estrategias para la logica de kitchen.
```php
<?php

namespace Kitchen\Service;

use Kitchen\DTOs\StoreDTO;

interface KitchenService {
    public function selectRandomRecipe(): StoreDTO;
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
```
## `KitchenServiceImpl.php`
Este archivo contiene toda la logica de manejo de mensajes.
```php
<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Support\Facades\Log;
use Kitchen\Repository\KitchenRepository;
use Order\DTOs\OrderDTO;

class KitchenServiceImpl implements KitchenService {
    private KitchenRepository $repository;
    private KitchenStrategyFactory $strategyFactory;
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        KitchenRepository $repository,
        KitchenStrategyFactory $strategyFactory,
        IRabbitMQKitchenProvider $provider
    ) {
        $this->repository = $repository;
        $this->strategyFactory = $strategyFactory;
        $this->provider = $provider;
    }

    public function initializeRabbitMQ(): void {
        Log::channel('console')->debug("Init exchange and binding for RabbitMQ Kitchen");
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        $this->provider->declareQueueWithBindings('kitchen_queue', 'order_exchange', '*.kitchen.*');
        Log::channel('console')->debug("Configuración de RabbitMQ completada.");
    }

    public function selectRandomRecipe(): StoreDTO {
        $recipes = RecipeNameEnum::getValues();
        $recipeName = $recipes[array_rand($recipes)];
        $ingredients = $this->repository->getIngredientsByRecipe($recipeName);
        $storeDTO = StoreDTO::fromRecipe(
            0,
            $recipeName,
            $ingredients
        );
        return $storeDTO;
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'kitchen_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $routingKey = $message->get('routing_key');

                if (str_starts_with($routingKey, 'order.')) {
                    $storeDTO = $this->selectRandomRecipe();
                    $storeDTO->orderId = $data['orderId'];
                    $this->processOrderMessage($storeDTO);
                } elseif (str_starts_with($routingKey, 'store.')) {
                    $this->processStoreMessage($data);
                } else {
                    Log::warning("Unrecognized routing key: {$routingKey}");
                }
            },
        ]);
    }

    private function processOrderMessage(StoreDTO $storeDTO): void {
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }

    private function processStoreMessage(StoreDTO $storeDTO): void {
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }
}
```
## `KitchenStrategy.php`
Este archivo contiene el contrato de estrategias para el manejo de pedidos en la cocina.
```php
<?php

namespace Kitchen\Strategies\Kitchen;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;

interface KitchenStrategy {
    public function getType(): StoreAvailabilityEnum;
    public function apply(StoreDTO $storeDTO): void;
}
```
## `NotAvailableIngredientsStrategy.php`
Este archivo contiene la estrategia para cuando no existan ingredientes disponibles.
```php
<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;
use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Order\DTOs\OrderDTO;

class NotAvailableIngredientsStrategy implements KitchenStrategy {
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        IRabbitMQKitchenProvider $provider
    ) {
        $this->provider = $provider;
    }


    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("Publish to kitchen_exchange Order Not Available.");
        $this->publishToOrder($storeDTO);
        Log::info("Theres not available ingredients. Recipe: '{$storeDTO->recipeName}', consulting to store again.");
    }

    private function publishToOrder(StoreDTO $storeDTO): void {
        $orderDTO = OrderDTO::from([
            "orderId"=> $storeDTO->orderId,
            "recipeName"=> $storeDTO->recipeName,
            "status" => $storeDTO->status,
        ]);
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                $orderDTO
            ],
        ]);
    }

    private function publishToStore(StoreDTO $storeDTO): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'store.kitchen',
            'message' => [
                $storeDTO
            ],
        ]);
    }
}
```
## `AvailableIngredientsStrategy.php`
Este archivo contiene la estrategia para cuando no existan ingredientes disponibles.
```php
<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Illuminate\Support\Facades\Log;
use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;

class AvailableIngredientsStrategy implements KitchenStrategy {
    private IRabbitMQKitchenProvider $provider;

    public function __construct(
        IRabbitMQKitchenProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::info("Publish to kitchen_exchange Order Available.");
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
                'status' => OrderStatusEnum::LISTO,
            ],
        ]);

        Log::info("Recipe '{$storeDTO->recipeName}' for order ID: {$storeDTO->orderId}. Ready!");
    }
}
```
## `RabbitMQStrategy.php`
Este archivo contiene el contrato de las estrategias que seran implementadas.
```php
<?php

namespace Kitchen\Strategies\RabbitMQ;

interface RabbitMQStrategy {
    public function getType(): string;
    public function execute(array $params): void;
}
```
## `ConsumeStrategy.php`
Este archivo contiene la estrategia de consumo de mensajes.
```php
<?php

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
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
        $params['channel']->queue_declare($params['queue'], false, true, false, false);
        $params['channel']->queue_bind($params['queue'], 'order_exchange', '*.kitchen.*');
        $this->logger->info('QUEUE "kitchen_exchange" correctly declared.');
        $this->logger->info('QUEUE "kitchen_exchange" binding other exchanges with routing "*.kitchen.*".');

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

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
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
            $params['exchange'],   // Usamos el exchange dinámicamente
            $params['routingKey']  // Usamos el routingKey dinámicamente
        );
        Log::channel('console')->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);
        $this->logger->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);

    }
}
```
