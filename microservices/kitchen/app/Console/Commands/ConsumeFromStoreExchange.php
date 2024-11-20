<?php

namespace Kitchen\Console\Commands;

use Kitchen\DTOs\StoreDTO;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use Kitchen\Service\KitchenService;
use Illuminate\Console\Command;

class ConsumeFromStoreExchange extends Command {
    protected $signature = 'rabbitmq:consume-store';
    protected $description = 'Consume messages from RabbitMQ for store.';
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
            'queue' => 'store.kitchen',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $storeDTO = StoreDTO::fromArray($data);

                // Aplicar la estrategia
                $this->kitchenService->prepareDish($storeDTO);

                if ($storeDTO->ingredientsInStore) {
                    $this->publishForOrderMicroService($storeDTO);
                }
            },
        ]);
        $this->provider->executeStrategy('consume', [
            'queue' => 'store.kitchen',
            'callback' => function ($message) {
                $data = json_decode($message->getBody(), true);
                $storeDTO = StoreDTO::fromArray($data);

                $this->kitchenService->prepareDish($storeDTO);
            },
        ]);
    }

    public function publishForOrderMicroService(StoreDTO $storeDTO): void {
        $this->provider->executeStrategy('publish', [
            'channel' => $this->provider->getChannel(),
            'exchange' => 'order_exchange',
            'routingKey' => 'order.kitchen',
            'message' => [
                'orderId' => $storeDTO->orderId,
                'recipeName' => $storeDTO->recipeName,
            ],
        ]);
    }
}
