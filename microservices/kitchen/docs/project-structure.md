# Proyecto de Microservicio Kitchen

## Descripción general

Este microservicio se encarga de recibir un pedido, elegir una receta, publicar a Microservicio Order y Microservicio Store sobre la receta, y consumir respuesta de store, si hay ingredientes suficientes pasa el pedido listo a Microservicio Order. 

## Estructura del Proyecto y directorio actual 

### Path: `./prueba_tecnica_alegra_oscar_ramirez/microservices/kitchen/`
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
Este archivo contiene la logica de incializacion de rabbitmq delegando la responsabilidad al microservicio.
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
Enums para ingredientes, que deberia ser comun entre microservicios para standarizar la comunicacion.
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
Enums para ingredientes, tambien debe ser incluido en los distintos microservicios, para standarizar el manejo de platos.
```php
<?php

namespace Kitchen\Enums;

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
## `StoreAvailabilityEnum.php`
Enums para manejar disponibilidades de ingredientes, actualmente la logica lo maneja Kitchen. Pero considero que `Store` debe validar la disponabilidad y responder un dto que contenga este enum, y `Kitchen` en vez de analizar esto, simplemente ejecuta la estrategia correspondiente.
```php
<?php

namespace Kitchen\Enums;

enum StoreAvailabilityEnum: string {
    case AVAILABLE = 'Available Ingredients';
    case NOT_AVAILABLE = 'Not Available Ingredients';
}
```
## `OrderDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Order, sirve de comunicacion entre `Kitchen` y `Order` para comunicar el estado de los platos.
```php
<?php

namespace Kitchen\DTOs;

use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Enums\RecipeNameEnum;
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
## `StoreDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Store, y a este dto creo que hay que agregar el enum de disponibilidad, para que `Kitchen` a partir de eso sepa que estrategia usar. Se delega la responsabilidad de determinar disponibilidad a `Store`.
El metodo `hasSufficientStock` deberia pertenecer a otra abstraccion, deberia ser responsabilidad de `Store` no de `Kitchen`.
```php
<?php

namespace Kitchen\DTOs;

use Illuminate\Support\Facades\Log;
use Kitchen\Enums\IngredientEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipeName,

        /** @var array<array{name: string, quantity_available: int}> */
        public array $ingredients,

        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) { }

    // metodo a redistribuir y refactorizar.
    public function hasSufficientStock(): bool {
        Log::channel("console")->info("ingredientes ahora: ", [""=>$this->ingredients]);
        return collect($this->ingredients)
            ->every(fn($ingredient) => $ingredient->quantity_available > 0);
    }
}
```
## `RabbitMQStrategyFactory.php`
Este archivo contiene el patron de fabrica para determinar cual sera la estrategia a implementar para el proveedor de rabbitmq, actualmente estas estrategias estan standarizadas para todos los microservicios. En el futuro podria ser una libreria, por ahora se replica tal cual para cada microservicio.
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
Este archivo contiene los metodos para el provider de rabbitmq, el cual esta standarizado para la inicializacion de rabbitmq en todos los microservicios.
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
Archivo con la implementacion de metodos para provider RabbitMQ, tambien esta standarizado para todos los microservicios.
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
Este archivo contiene los metodos para consultas a base de datos en `Kitchen`
```php
<?php

namespace Kitchen\Repository;

interface KitchenRepository {
    public function getIngredientsByRecipe(string $recipeName): array;
}
```
## `KitchenRepositoryImpl.php`
Este archivo contiene los metodos para consultas a base de datos, aqui obtenemos los ingredientes que requieren cada receta.
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
                ->map(function ($ingredient) {
                    return [
                        'ingredient_name' => $ingredient->ingredient_name,
                        'quantity_required' => $ingredient->quantity_required,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            throw $e;
        };
    }
}```
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
Este archivo contiene toda la logica de manejo de mensajes, aqui es donde debemos decidir la estrategia a partir de lo que notifique `Store` pero en tanto no suceda eso, debemos comunicar a `Order` que ya recibimos el pedido, y le notificamos que el estado pasa a `ESPERANDO`, cuando consumimos mensaje con ingredientes disponibles, ahi avisamos a `Order` que estamos preparando plato, simulamos un tiempo de 5 segundos, y avisamos que el plato esta listo. 
```php
<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\OrderDTO;
use Kitchen\DTOs\RecipeDTO;
use Kitchen\DTOs\StoreDTO;
use Kitchen\Entities\RecipeEntity;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Mappers\RecipeMapper;
use Kitchen\Mappers\StoreDTOMapper;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Support\Facades\Log;
use Kitchen\Repository\KitchenRepository;

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
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        /**
         * FIXME
         * Find an approach in order to avoid declaration of exchanges, without depending on other microservices.
         * Exchanges are idempotent, so they are not created if they already exist
         */
        $this->provider->declareExchange('order_exchange', 'topic');
        $this->provider->declareQueueWithBindings('kitchen_queue', 'order_exchange', '*.kitchen.*');
        Log::channel('console')->debug("Configuración de RabbitMQ completada.");
    }

    public function selectRandomRecipe(): RecipeDTO {
        $recipeEntity = RecipeEntity::with('ingredients')->inRandomOrder()->first();
        $recipeDTO = RecipeMapper::entityToDTO($recipeEntity);
        return $recipeDTO;
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'kitchen_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $orderDTO = OrderDTO::from($data);
                $routingKey = $message->get('routing_key');
                if (str_starts_with($routingKey, 'order.')) {
                    $recipeDTO = $this->selectRandomRecipe();
                    $storeDTO = StoreDTOMapper::fromRecipeDTO($recipeDTO, $orderDTO->orderId);
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
Este archivo contiene la estrategia para cuando no existan ingredientes disponibles, aqui volvemos a notificar a `Store` para que se entere que seguimos esperando el plato, por si haya tenido algun incoveniente en manejar la solicitud. Y avisameos a `Order` que estamos `ESPERANDO`.
```php
<?php

namespace Kitchen\Strategies\Kitchen\Concrete;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use Kitchen\Enums\OrderStatusEnum;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\DTOs\OrderDTO;

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
        $this->publishToOrder($storeDTO);
    }

    private function publishToOrder(StoreDTO $storeDTO): void {
        $orderDTO = new OrderDTO(
            $storeDTO->orderId,
            RecipeNameEnum::from($storeDTO->recipeName),
            OrderStatusEnum::ESPERANDO,
        );
        $message = json_encode($orderDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'order.kitchen',
            'message' => $message,
        ]);
    }

    private function publishToStore(StoreDTO $storeDTO): void {
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'kitchen_exchange',
            'routingKey' => 'store.kitchen',
            'message' => $message,
        ]);
    }
}
```
## `AvailableIngredientsStrategy.php`
Este archivo contiene la estrategia para cuando existen ingredientes disponibles, aqui debemos hacer la simulacion de 5 segundos, pasar estado `PROCESANDO` y por ultimo estado `LISTO`.
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
            $params['exchange'],
            $params['routingKey']
        );
        Log::channel('console')->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);
        $this->logger->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);

    }
}
```
