<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\RecipeNameEnum;
use App\Factories\OrderDTOFactory;
use App\Providers\Interfaces\IRabbitMQProvider;
use App\Services\Order\OrderService;
use Illuminate\Console\Command;

class ConsumeOrderMessages extends Command {
    private IRabbitMQProvider $messageQueueProvider;
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

    public function __construct(IRabbitMQProvider $messageQueueProvider, OrderService $orderService) {
        parent::__construct();
        $this->messageQueueProvider = $messageQueueProvider;
        $this->orderService = $orderService;
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->messageQueueProvider->consume('order.kitchen', function ($message) {
            $messageBody = $message->getBody();    
            $orderData = json_decode($messageBody, true);

            if (isset($orderData['orderId']) && isset($orderData['recipeName'])) {
                try {
                    $recipeName = $orderData['recipeName'];
                    if (!in_array($recipeName, RecipeNameEnum::getValues())) {
                        $this->error("Invalid Recipe: {$recipeName}");
                        return;
                    }

                    $orderDTO = OrderDTOFactory::createOrderDTO([
                        'orderId' => $orderData['orderId'],
                        'recipeName' => RecipeNameEnum::from($orderData['recipeName']),
                        'status' => OrderStatusEnum::PROCESANDO,
                    ]);

                    $this->orderService->updateOrderRecipe($orderDTO);
                    $this->info("Receta actualizada para la orden ID: {$orderData['orderId']}");

                } catch (\Exception $e) {
                    $this->error('Error al actualizar la receta: ' . $e->getMessage());
                }
            }
        });
    }

}
