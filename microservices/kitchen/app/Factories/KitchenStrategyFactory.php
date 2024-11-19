<?php

namespace App\Factories;

use App\DTOs\StoreDTO;
use App\Enums\StoreAvailabilityEnum;
use App\Enums\StoreIngredientsEnum;
use App\Strategy\KitchenStrategy;
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

    public function getStrategy(StoreDTO $storeResponse): KitchenStrategy {
        $allIngredientsAvailable = collect($storeResponse->ingredientsInStore)
            ->every(fn($ingredient) => $ingredient['current_stock'] >= $ingredient['quantity_required']);

        $type = $allIngredientsAvailable
            ? StoreAvailabilityEnum::AVAILABLE
            : StoreAvailabilityEnum::NOT_AVAILABLE;

        if (!isset($this->strategies[$type->value])) {
            throw new InvalidArgumentException("No strategy found for type: {$type->value}");
        }

        return $this->strategies[$type->value];
    }
}
