<?php

namespace Store\Factories;

use Illuminate\Support\Facades\Log;
use Store\DTOs\StoreDTO;
use Store\Strategies\Store\StoreStrategy;
use InvalidArgumentException;

class StoreStrategyFactory {
    private array $strategies;

    public function __construct(array $strategies) {
        $this->strategies = [];
        foreach ($strategies as $strategy) {
            if($strategy instanceof StoreStrategy) {
                $this->strategies[ $strategy->getType()->value ] = $strategy;
            }
        }
    }

    public function getStrategy(StoreDTO $storeDTO): StoreStrategy {
        Log::channel("console")->debug("Obteniendo estrategia para", ["availability"=>$storeDTO->availability]);
        $type = $storeDTO->availability;

        if (!isset($this->strategies[$type->value])) {
            throw new InvalidArgumentException("No strategy found for type: {$type->value}");
        }

        return $this->strategies[$type->value];
    }

    public static function dummyStrategyAvailable(): StoreDTO {
        return StoreDTO::from(
            orderId: 1,
            recipeName: 'hamburguesa',
            ingredients: [
                ['ingredient_name' => 'meat', 'quantity_available' => 2],
                ['ingredient_name' => 'lettuce', 'quantity_available' => 2],
                ['ingredient_name' => 'tomato', 'quantity_available' => 2],
                ['ingredient_name' => 'onion', 'quantity_available' => 2],
            ]
        );
    }

    public static function dummyStrategyNotAvailable(): StoreDTO {
        return StoreDTO::from(
            orderId: 2,
            recipeName: 'hamburguesa',
            ingredients: [
                ['ingredient_name' => 'meat', 'quantity_available' => 0],
                ['ingredient_name' => 'lettuce', 'quantity_available' => 0],
                ['ingredient_name' => 'tomato', 'quantity_available' => 0],
                ['ingredient_name' => 'onion', 'quantity_available' => 0],
            ]
        );
    }
}
