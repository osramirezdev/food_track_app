# Proyecto de Microservicio Store

## Descripción general

Este microservicio se encarga de consumir ingredientes de Kitchen, verificr si existen en stock, caso que existan en stock proveerlos a cocina, de lo contrario se encarga de comprar y por ultimo proveer de nuevo a la cocina. 

## Estructura del Proyecto y directorio actual 

### Path: `./prueba_tecnica_alegra_oscar_ramirez/microservices/store/`
```bash
app/
├── Console
│   ├── Commands
│   │   └── ConsumeStoreMessages.php
│   └── Kernel.php
├── DTOs
│   ├── IngredientDTO.php
│   ├── RecipeDTO.php
│   └── StoreDTO.php
├── Entities
│   └── IngredientsEntity.php
├── Enums
│   ├── IngredientEnum.php
│   ├── RecipeNameEnum.php
│   └── StoreAvailabilityEnum.php
├── Factories
│   ├── RabbitMQStrategyFactory.php
│   └── StoreStrategyFactory.php
├── Http
│   └── Controllers
│       └── Controller.php
├── Mappers
│   ├── IngredientMapper.php
│   └── StoreDTOMapper.php
├── Models
│   └── User.php
├── Providers
│   ├── AppServiceProvider.php
│   ├── Interfaces
│   │   └── IRabbitMQProvider.php
│   └── RabbitMQProvider.php
├── Proxy
│   └── MarketProxy.php
├── Repositories
│   ├── Impl
│   │   └── StoreRepositoryImpl.php
│   └── StoreRepository.php
├── Service
│   ├── Impl
│   │   └── StoreServiceImpl.php
│   └── StoreService.php
└── Strategies
    ├── RabbitMQ
    │   ├── Concrete
    │   │   ├── ConsumeStrategy.php
    │   │   └── PublishStrategy.php
    │   └── RabbitMQStrategy.php
    └── Store
        ├── Concrete
        │   ├── AvailableIngredientsStrategy.php
        │   └── NotAvailableIngredientsStrategy.php
        └── StoreStrategy.php
```
## Descripción de Archivos y Funcionalidades

## `ConsumeStoreMessages.php`
Este archivo contiene la logica de incializacion de rabbitmq delegando la responsabilidad al microservicio.
```php
<?php

namespace Store\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Store\Service\StoreService;

class ConsumeStoreMessages extends Command {
    protected $signature = 'rabbitmq:consume-store';
    protected $description = 'Consume messages for store service.';
    private StoreService $storeService;

    public function __construct(
        StoreService $storeService
    ) {
        parent::__construct();
        $this->storeService = $storeService;
    }

    public function handle() {
        Log::channel('console')->info("Init store consumer");
        $this->storeService->initializeRabbitMQ();
        $this->storeService->processMessages();
    }
}
```
## `IngredientEnum.php`
Enums para ingredientes, que deberia ser comun entre microservicios para standarizar la comunicacion.
```php
<?php

namespace Store\Enums;

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
## `RecipeNameEnum.php`
Enums para ingredientes, tambien debe ser incluido en los distintos microservicios, para standarizar el manejo de platos.
```php
<?php

namespace Store\Enums;

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
Enums para manejar disponibilidades de ingredientes. `Store` debe validar la disponabilidad y responder un dto que contenga este enum.
```php
<?php

namespace Store\Enums;

enum StoreAvailabilityEnum: string {
    case AVAILABLE = 'Available Ingredients';
    case NOT_AVAILABLE = 'Not Available Ingredients';
}
```
## `StoreDTO.php`
Este archivo contiene el objeto para manejo de datos de MS Store, el cual sirve para comunicar datos entre `Kitchen` y `Store`.
```php
<?php

namespace Store\DTOs;

use Illuminate\Support\Facades\Log;
use Store\Enums\StoreAvailabilityEnum;
use Spatie\LaravelData\Data;

class StoreDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipeName,

        /** @var array<array{name: string, quantity_required: int, quantity_available: int}> */
        public array $ingredients,
        public ?bool $checked = false,
        public ?StoreAvailabilityEnum $availability = StoreAvailabilityEnum::NOT_AVAILABLE,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) { }

}
```
## `RecipeDTO.php`
Este archivo contiene el objeto para manejo de recetas entre MS `Store` y `Kitchen`.
```php
<?php

namespace Store\DTOs;

use Spatie\LaravelData\Data;

class RecipeDTO extends Data {

    public function __construct(
        public ?int $orderId,
        public string $recipe,
        /** @var array<array{ingredient_name: string, quantity_available: int}> */
        public array $ingredients,
    ) { }

}
```
## `Ingredient.php`
Este archivo contiene el objeto para manejo de ingredientes entre MS `Store` y `Kitchen`.
```php
<?php

namespace Store\DTOs;

use Spatie\LaravelData\Data;

class IngredientDTO extends Data {

    public function __construct(
        public string $ingredient_name,
        public ?int $quantity_required,
        public ?int $quantity_available = 0,
    ) { }

}
```
## `RabbitMQStrategyFactory.php`
Este archivo contiene el patron de fabrica para determinar cual sera la estrategia a implementar para el proveedor de rabbitmq, actualmente estas estrategias estan standarizadas para todos los microservicios. En el futuro podria ser una libreria, por ahora se replica tal cual para cada microservicio.
```php
<?php

namespace Store\Factories;

use Store\Strategies\RabbitMQ\RabbitMQStrategy;
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
Este archivo contiene los metodos para el provider de rabbitmq, el cual esta standarizado para la inicializacion de rabbitmq en todos los microservicios.
```php
<?php

namespace Store\Providers\Interfaces;

interface IRabbitMQProvider {
    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void;
    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void;
    public function executeStrategy(string $type, array $params): void;
    public function getChannel();
}
```
## `RabbitMQProvider.php`
Archivo con la implementacion de metodos para provider RabbitMQ, tambien esta standarizado para todos los microservicios.
```php
<?php

namespace Store\Providers;

use Exception;
use Illuminate\Support\Facades\Log;
use Store\Providers\Interfaces\IRabbitMQProvider;
use Store\Factories\RabbitMQStrategyFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQProvider implements IRabbitMQProvider {
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
## `StoreRepository.php`
Este archivo contiene los metodos para consultas a base de datos en `Store`.
```php
<?php

namespace Store\Repositories;

interface StoreRepository {
    public function getIngredientStock(string $ingredientName): int;
    public function updateIngredientStock(string $ingredientName, int $newStock): void;
    public function updateIngredientStocks(array $ingredients): void;
    public function getAvailableIngredients(array $ingredientNames): array;
}
```
## `StoreRepositoryImpl.php`
Este archivo contiene los metodos para consultas a base de datos.
```php
<?php

namespace Store\Repositories\Impl;

use Illuminate\Support\Facades\Log;
use Store\Entities\IngredientsEntity;
use Store\Mappers\IngredientMapper;
use Store\Repositories\StoreRepository;

class StoreRepositoryImpl implements StoreRepository {
    public function getIngredientStock(string $ingredientName): int {
        try{
            $ingredientEntity = IngredientsEntity::where('ingredient_name', $ingredientName)->first();
            return $ingredientEntity?->current_stock ?? 0;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateIngredientStock(string $ingredientName, int $newStock): void {
        try{
            Log::channel("console")->debug("updateIngredientStock:", [
                "ingredientName" => $ingredientName
            ]);
            $entity = IngredientsEntity::firstOrNew(['ingredient_name' => $ingredientName]);
            $entity->current_stock = $newStock;
            $entity->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateIngredientStocks(array $ingredients): void {
        try{
            $entities = IngredientMapper::dtosToEntities($ingredients);

            foreach ($entities as $entity) {
                $entity->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAvailableIngredients(array $ingredientNames): array {
        try{
            return IngredientsEntity::whereIn('ingredient_name', $ingredientNames)->get()->all();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
```
## `StoreService.php`
Este archivo contiene el contrato de estrategias para la logica de Store, por ahora solo tiene llogica para incializar rabbit y consumir mensajes.
```php
<?php

namespace Store\Service;

interface StoreService {
    public function processMessages(): void;
    public function initializeRabbitMQ(): void;
}
```
## `StoreServiceImpl.php`
Este archivo contiene toda la logica de negocios para manejar el stock de ingredientes, la compra debe ser responsabilidad de `Purchase` pero queda pendiente de implementacion. 
```php
<?php

namespace Store\Service\Impl;

use Store\DTOs\StoreDTO;
use Store\Factories\StoreStrategyFactory;
use Store\Providers\Interfaces\IRabbitMQProvider;
use Store\Service\StoreService;
use Illuminate\Support\Facades\Log;
use Store\Enums\StoreAvailabilityEnum;
use Store\DTOs\IngredientDTO;
use Store\Entities\IngredientsEntity;
use Store\Mappers\IngredientMapper;
use Store\Proxy\MarketProxy;
use Store\Repositories\StoreRepository;

class StoreServiceImpl implements StoreService {
    private StoreRepository $repository;
    private StoreStrategyFactory $strategyFactory;
    private IRabbitMQProvider $provider;
    private MarketProxy $proxy;

    public function __construct(
        StoreRepository $repository,
        StoreStrategyFactory $strategyFactory,
        IRabbitMQProvider $provider,
        MarketProxy $proxy
    ) {
        $this->repository = $repository;
        $this->strategyFactory = $strategyFactory;
        $this->provider = $provider;
        $this->proxy = $proxy;
    }

    public function initializeRabbitMQ(): void {
        $this->provider->declareExchange('store_exchange', 'topic');
        /**
         * FIXME
         * Find an approach in order to avoid declaration of exchanges, without depending on other microservices.
         * Exchanges are idempotent, so they are not created if they already exist
         */
        $this->provider->declareExchange('kitchen_exchange', 'topic');
        $this->provider->declareQueueWithBindings('store_queue', 'kitchen_exchange', 'store.kitchen');
        Log::channel('console')->debug("Configuración de RabbitMQ completada.");
    }

    public function buyIngredient(IngredientDTO $ingredient): IngredientDTO {
        Log::channel("console")->debug("buy ingredient", [
            "IngredientDTO" => $ingredient,
        ]);
        $qPurchased = $this->proxy->purchaseIngredient($ingredient->ingredient_name);
        
        if ($qPurchased > 0) {
            $ingredient->quantity_available += $qPurchased;
            $this->repository->updateIngredientStock($ingredient->ingredient_name, $ingredient->quantity_available);
        }
    
        Log::info("Buying {$qPurchased} of {$ingredient->ingredient_name}.");
        return $ingredient;
    }

    public function processMessages(): void {
        $this->provider->executeStrategy('consume', [
            'channel' => $this->provider->getChannel(),
            'queue' => 'store_queue',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $storeDTO = StoreDTO::from($data);

                $this->processStoreMessage($storeDTO);
            },
        ]);
    }

    private function processStoreMessage(StoreDTO $storeDTO): void {
        Log::channel("console")->debug("Procesing inventory");
        $this->handleInventory($storeDTO);
    }

    private function handleInventory(StoreDTO $storeDTO): void {
        $ingredientNames = array_map(fn($ingredient) => $ingredient['ingredient_name'], $storeDTO->ingredients);
        $inventoryEntities = $this->repository->getAvailableIngredients($ingredientNames);
        // merge de ingredients with db
        $inventoryDTOs = IngredientMapper::mapWithRequired($storeDTO->ingredients, $inventoryEntities);
        Log::channel("console")->debug("recipe", ["recipeName"=>$storeDTO->recipeName]);
        Log::channel("console")->debug("requirement", ["inventoryDTOs"=>$inventoryDTOs]);
        
        $hasSufficientStock = true; // flag :(
        foreach ($inventoryDTOs as $ingredientDTO) {
            Log::channel("console")->debug("Availability", [
                "ingredient" => $ingredientDTO->ingredient_name,
                "required" => $ingredientDTO->quantity_required,
                "available" => $ingredientDTO->quantity_available
            ]);
            if ($ingredientDTO->quantity_available < $ingredientDTO->quantity_required) {
                $hasSufficientStock = false;
                $ingredientDTO = $this->buyIngredient($ingredientDTO);
            }
        }
        $storeDTO->availability = $hasSufficientStock 
        ? StoreAvailabilityEnum::AVAILABLE 
        : StoreAvailabilityEnum::NOT_AVAILABLE;

        if ($hasSufficientStock && !$storeDTO->checked) {
            $this->discountInventory($storeDTO->ingredients);
        }
        Log::channel("console")->debug("stocke ", ["hay stock" => $hasSufficientStock]);
        
        Log::channel("console")->debug("Estado final de availability", ["availability" => $storeDTO->availability]);
        $storeDTO->checked = true;
        $this->applyStrategy($storeDTO);
    }

    private function discountInventory(array $ingredients): void {
        Log::channel("console")->debug("discount", ["ingredients"=>$ingredients]);
        $ingredientEntities = IngredientMapper::dtosToEntities(
            array_map(fn($ingredient) => new IngredientDTO(
                $ingredient['ingredient_name'],
                $ingredient['quantity_required'],
                max(0, $ingredient['quantity_available'] - $ingredient['quantity_required'])
            ), $ingredients)
        );

        Log::channel("console")->debug("discount", ["ingredientEntities"=>$ingredientEntities]);
    
        foreach ($ingredientEntities as $entity) {
            $this->repository->updateIngredientStock($entity->ingredient_name, $entity->current_stock);
        }
    
        Log::channel("console")->info("Store stock decremented.", ["ingredients" => $ingredients]);
    }

    private function applyStrategy(StoreDTO $storeDTO): void {
        Log::channel("console")->debug("deberia publicar a kitchen", ["storeDTO"=>$storeDTO]);
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }
    
}
```
## `StoreStrategy.php`
Este archivo contiene el contrato de estrategias para el manejo de pedidos de ingredientes por parte de `Kitchen`.
```php
<?php

namespace Store\Strategies\Store;

use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;

interface StoreStrategy {
    public function getType(): StoreAvailabilityEnum;
    public function apply(StoreDTO $storeDTO): void;
}
```
## `NotAvailableIngredientsStrategy.php`
Este archivo contiene la estrategia para cuando no existan ingredientes suficientes, aqui volvemos a notificar a `Kitchen` con `StoreAvailabilityEnum::NOT_AVAILABLE` para que se entere que no hay ingredientes, e invocamos al metodo de compra a `API`, y luego actualizamos el stock.
```php
<?php

namespace Store\Strategies\Store\Concrete;

use Illuminate\Support\Facades\Log;
use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;
use Store\Strategies\Store\StoreStrategy;
use Store\Providers\Interfaces\IRabbitMQProvider;

class NotAvailableIngredientsStrategy implements StoreStrategy {
    private IRabbitMQProvider $provider;

    public function __construct(
        IRabbitMQProvider $provider
    ) {
        $this->provider = $provider;
    }


    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::NOT_AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        $this->publishToKitchen($storeDTO);
    }

    private function publishToKitchen(StoreDTO $storeDTO): void {
        Log::channel('console')->info("Publish to kitchen_exchange Order Not Available.");
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'store_exchange',
            'routingKey' => 'store.kitchen.*',
            'message' => $message,
        ]);
    }
}
```
## `AvailableIngredientsStrategy.php`
Este archivo contiene la estrategia para cuando existen ingredientes disponibles, avisamos a `Kitchen` que hay ingredientes disponibles para que prepare el plato, invocamos a servicio que descuenta de base de datos la cantidad de ingredientes utilizada.
```php
<?php

namespace Store\Strategies\Store\Concrete;

use Store\DTOs\StoreDTO;
use Store\Enums\StoreAvailabilityEnum;
use Store\Strategies\Store\StoreStrategy;
use Illuminate\Support\Facades\Log;
use Store\Providers\Interfaces\IRabbitMQProvider;

class AvailableIngredientsStrategy implements StoreStrategy {
    private IRabbitMQProvider $provider;

    public function __construct(
        IRabbitMQProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function getType(): StoreAvailabilityEnum {
        return StoreAvailabilityEnum::AVAILABLE;
    }

    public function apply(StoreDTO $storeDTO): void {
        Log::channel('console')->info("Publish to kitchen_exchange Order Available.");
        $storeDTO->availability = StoreAvailabilityEnum::AVAILABLE;
        $message = json_encode($storeDTO->toArray());
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'store_exchange',
            'routingKey' => 'store.kitchen.*',
            'message' => $message,
        ]);
    }
}
```
## `RabbitMQStrategy.php`
Este archivo contiene el contrato de las estrategias que seran implementadas.
```php
<?php

namespace Store\Strategies\RabbitMQ;

interface RabbitMQStrategy {
    public function getType(): string;
    public function execute(array $params): void;
}
```
## `ConsumeStrategy.php`
Este archivo contiene la estrategia de consumo de mensajes.
```php
<?php

namespace Store\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Store\Strategies\RabbitMQ\RabbitMQStrategy;
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

namespace Store\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Store\Strategies\RabbitMQ\RabbitMQStrategy;
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
## `MarketProxy.php`
Este archivo aplica patron `Proxy` para no romper la arquitectura de `MS` ya que permite a `Store` interactuar con otros servicios externos sin comprometer su desacoplamiento ni introducir dependencias directas entre módulos o servicios.
```php
<?php

namespace Store\Proxy;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class that apply Proxy pattern
 *
 * @author oramirez
 * @since 24/11/24
 */
class MarketProxy {
    private string $baseUrl;

    public function __construct() {
        $this->baseUrl = config('services.market.url');
    }

    public function purchaseIngredient(string $ingredient): int {
        $url = "{$this->baseUrl}/api/farmers-market/buy";

        $response = Http::get($url, [
            'ingredient' => $ingredient,
        ]);

        if ($response->failed()) {
            Log::error("Error buying ingredient: {$ingredient}", ['response' => $response->body()]);
            throw new \Exception("Purchase can");
        }

        $data = $response->json();
        return $data['quantitySold'] ?? 0;
    }
}
```
## `StoreDTOMapper.php`
Este archivo contiene el patron de disenho `Adapter` que nos permite hacer de puente entre objetos.
```php
<?php

namespace Store\Mappers;

use kepka42\LaravelMapper\Mapper\AbstractMapper;
use Store\DTOs\IngredientDTO;
use Store\DTOs\RecipeDTO;
use Store\DTOs\StoreDTO;

class StoreDTOMapper extends AbstractMapper {
    protected $sourceType = "";

    protected $hintType = StoreDTO::class;

    public function map($object, $params = []): StoreDTO {
        return StoreDTO::from(
            $data['orderId'] ?? null,
            $data['recipe'] ?? 'unknown',
            $data['ingredients'] ?? []
        );
    }

    public static function fromRecipeDTO(RecipeDTO $recipeDTO, ?int $orderId = null): StoreDTO {
        $ingredients = collect($recipeDTO->ingredients)
        ->map(function (IngredientDTO $ingredient) {
            return new IngredientDTO(
                $ingredient->ingredient_name,
                $ingredient->quantity_required,
            );
        })
        ->all();

        return new StoreDTO(
            $orderId,
            $recipeDTO->recipe,
            $ingredients
        );
    }

}
```
## `IngredientMapper.php`
Este archivo contiene un `Adapter` para manejar la manipulacion de estructura de datos entre `Kitchen` y `Store` con respecto a los ingredientes.
```php
<?php

namespace Store\Mappers;

use Store\Entities\IngredientsEntity;
use Store\DTOs\IngredientDTO;

class IngredientMapper {

    public static function entityToDto(IngredientsEntity $entity, ?int $required = 0): IngredientDTO {
        $ingredientDTO = new IngredientDTO(
            ingredient_name: $entity->ingredient_name,
            quantity_required: $required,
            quantity_available: $entity->current_stock ?? 0
        );

        return $ingredientDTO;
    }

    public static function dtoToEntity(IngredientDTO $dto): IngredientsEntity {
        $ingredientEntity = IngredientsEntity::firstOrNew(['ingredient_name' => $dto->ingredient_name]);
        $ingredientEntity->current_stock = $dto->quantity_available;
        return $ingredientEntity;
    }

    public static function entitiesToDtos($ingredientsEntities): array {
        $dtos = collect($ingredientsEntities)
        ->map(fn(IngredientsEntity $entity) => self::entityToDto($entity))
        ->all();
        return $dtos;
    }

    public static function dtosToEntities(array $dtos): array {
        $entities = collect($dtos)
            ->map(fn(IngredientDTO $dto) => self::dtoToEntity($dto))
            ->values()
            ->all();
        
        return $entities;
    }

    public static function mapWithRequired(array $ingredientsRequired, array $ingredientsEntities): array {
        $ingredientMap = collect($ingredientsEntities)
            ->keyBy(fn(IngredientsEntity $ingredientEntity) => $ingredientEntity->ingredient_name);
    
        return array_map(function ($ingredientRequired) use ($ingredientMap) {
            $ingredientName = $ingredientRequired['ingredient_name'];
            $quantityRequired = $ingredientRequired['quantity_required'];
    
            $ingredientEntity = $ingredientMap->get($ingredientName, new IngredientsEntity([
                'ingredient_name' => $ingredientName,
                'current_stock' => 0,
            ]));
    
            return self::entityToDto($ingredientEntity, $quantityRequired);
        }, $ingredientsRequired);
    }
}
```