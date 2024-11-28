<?php

namespace Store\Service\Impl;

use Illuminate\Database\Eloquent\Collection;
use Store\DTOs\StoreDTO;
use Store\Factories\StoreStrategyFactory;
use Store\Providers\Interfaces\IRabbitMQProvider;
use Store\Service\StoreService;
use Illuminate\Support\Facades\Log;
use Store\Enums\StoreAvailabilityEnum;
use Store\DTOs\IngredientDTO;
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

    public function getIngredients(): Collection {
        $orders = $this->repository->getAll();
        return $orders;
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
