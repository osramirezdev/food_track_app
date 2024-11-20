<?php

namespace Kitchen\Console\Commands;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Service\KitchenService;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Illuminate\Console\Command;

class ConsumeFromOrderExchange extends Command {
    protected $signature = 'rabbitmq:consume-order';
    protected $description = 'Consume messages from RabbitMQ for order.';

    private IRabbitMQKitchenProvider $provider;
    private KitchenService $kitchenService;

    public function __construct(
        IRabbitMQKitchenProvider $provider,
        KitchenService $kitchenService
    ) {
        parent::__construct();
        $this->provider = $provider;
        $this->kitchenService = $kitchenService;
    }

    public function handle(): void {
        $this->provider->executeStrategy('consume', [
            'queue' => 'order.kitchen',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);

                $recipeName = $this->kitchenService->selectRandomRecipe();

                $this->publishForStore($data['orderId'], $recipeName);
                $this->publishForOrder($data['orderId'], $recipeName);

                $this->info("Order processed for ID: {$data['orderId']} with recipe: $recipeName");
            },
        ]);
    }

    private function publishForStore(int $orderId, string $recipeName): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'store_exchange',
            'routingKey' => 'store.kitchen',
            'message' => [
                'orderId' => $orderId,
                'recipeName' => $recipeName,
            ],
        ]);
    }

    private function publishForOrder(int $orderId, string $recipeName): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'order_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $orderId,
                'recipeName' => $recipeName,
                'status' => 'PROCESANDO',
            ],
        ]);
    }
}
