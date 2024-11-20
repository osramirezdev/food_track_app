<?php

namespace Kitchen\Service\Impl;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Enums\RecipeNameEnum;
use Kitchen\Factories\KitchenStrategyFactory;
use Kitchen\Repository\KitchenRepository;
use Kitchen\Service\KitchenService;

class KitchenServiceImpl implements KitchenService {
    private KitchenRepository $repository;
    private KitchenStrategyFactory $strategyFactory;

    public function __construct(
        KitchenRepository $repository,
        KitchenStrategyFactory $strategyFactory
    ) {
        $this->repository = $repository;
        $this->strategyFactory = $strategyFactory;
    }

    public function selectRandomRecipe(): string {
        $recipes = RecipeNameEnum::getValues();
        return $recipes[array_rand($recipes)];
    }
    public function handleStoreResponse(array $response): void {}

    public function prepareDish(StoreDTO $storeDTO): void {
        $strategy = $this->strategyFactory->getStrategy($storeDTO);
        $strategy->apply($storeDTO);
    }
}
