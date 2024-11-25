<?php

namespace Order\Console\Commands;

use Order\Services\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeOrderMessages extends Command {

    protected $signature = 'rabbitmq:consume-orders';
    protected $description = 'Command description';

    private OrderService $orderService;


    public function __construct(
        OrderService $orderService
    ) {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function handle() {
        Log::channel('console')->info("Init Order consumer");
        $this->orderService->initializeRabbitMQ();
        $this->orderService->processMessages();
    }
}
