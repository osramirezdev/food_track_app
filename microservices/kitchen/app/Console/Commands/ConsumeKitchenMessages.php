<?php

namespace Kitchen\Console\Commands;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Service\KitchenService;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Illuminate\Console\Command;

class ConsumeKitchenMessages extends Command {
    protected $signature = 'rabbitmq:consume-kitchen';
    protected $description = 'Consume messages for kitchen service.';

    private KitchenService $kitchenService;

    public function __construct(KitchenService $kitchenService) {
        parent::__construct();
        $this->kitchenService = $kitchenService;
    }

    public function handle(): void {
        $this->kitchenService->processMessages();
    }
}