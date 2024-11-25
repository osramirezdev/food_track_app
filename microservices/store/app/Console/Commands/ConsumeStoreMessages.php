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
