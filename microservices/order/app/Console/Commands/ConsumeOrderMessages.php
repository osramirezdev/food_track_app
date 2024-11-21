<?php

namespace Order\Console\Commands;

use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
use Order\Factories\OrderDTOFactory;
use Order\Providers\Interfaces\IRabbitMQProvider;
use Order\Services\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeOrderMessages extends Command {
    private IRabbitMQProvider $provider;
    private OrderService $orderService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(IRabbitMQProvider $provider, OrderService $orderService) {
        parent::__construct();
        $this->provider = $provider;
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        Log::channel('console')->info("Init Order consumer");
        $this->orderService->initializeRabbitMQ();
        $this->provider->consume('order.kitchen', function ($message) {
            $orderData = json_decode($message->getBody(), true);
            Log::channel('console')->info("Data received", ["orderData" => $orderData]);

            if (isset($orderData['orderId'], $orderData['recipeName'])) {
                $this->processOrderMessage($orderData);
            }
        });
    }

    private function processOrderMessage(array $orderData): void {
        try {
            $orderDTO = OrderDTOFactory::createOrderDTO([
                'orderId' => $orderData['orderId'],
                'recipeName' => $orderData['recipeName'],
                'status' => OrderStatusEnum::PROCESANDO,
            ]);

            $this->orderService->updateOrderRecipe($orderDTO);
            $this->info("Order updated: {$orderData['orderId']} with recipe: {$orderData['recipeName']}");
        } catch (\Exception $e) {
            $this->error("Error updating order: " . $e->getMessage());
        }
    }
}
