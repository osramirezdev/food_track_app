<?php

namespace Kitchen\Factories;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\StoreAvailabilityEnum;
use Kitchen\Strategies\Kitchen\KitchenStrategy;
use InvalidArgumentException;

class KitchenStrategyFactory {
    private array $strategies;

    public function __construct(array $strategies) {
        $this->strategies = [];
        foreach ($strategies as $strategy) {
            if($strategy instanceof KitchenStrategy) {
                $this->strategies[ $strategy->getType()->value ] = $strategy;
            }
        }
    }

    public function getStrategy(StoreDTO $storeDTO): KitchenStrategy {
        $type = $storeDTO->hasSufficientStock()
            ? StoreAvailabilityEnum::AVAILABLE
            : StoreAvailabilityEnum::NOT_AVAILABLE;

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
                ['name' => 'meat', 'quantity_available' => 2],
                ['name' => 'lettuce', 'quantity_available' => 2],
                ['name' => 'tomato', 'quantity_available' => 2],
                ['name' => 'onion', 'quantity_available' => 2],
            ]
        );
    }

    public static function dummyStrategyNotAvailable(): StoreDTO {
        return StoreDTO::from(
            orderId: 2,
            recipeName: 'hamburguesa',
            ingredients: [
                ['name' => 'meat', 'quantity_available' => 0],
                ['name' => 'lettuce', 'quantity_available' => 0],
                ['name' => 'tomato', 'quantity_available' => 0],
                ['name' => 'onion', 'quantity_available' => 0],
            ]
        );
    }
}
