<?php

namespace App\Console\Commands;

// use App\Providers\Interfaces\IRabbitMQProvider;
// use App\Services\Kitchen\KitchenService;
use Illuminate\Console\Command;

class ConsumeKitchenMessages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-kitchen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from RabbitMQ for kitchen orders.';

    // private IRabbitMQProvider $messageQueueProvider;
    // private KitchenService $kitchenService;

    // public function __construct(IRabbitMQProvider $messageQueueProvider, KitchenService $kitchenService) {
    //     parent::__construct();
    //     $this->messageQueueProvider = $messageQueueProvider;
    //     $this->kitchenService = $kitchenService;
    // }
    /**
     * Execute the console command.
     */
    public function handle(): void{
        // $this->info("Listening to 'order_exchange' for new orders...");
        // $this->messageQueueProvider->consume('kitchen.queue', function ($message) {
        //     $payload = json_decode($message->getBody(), true);

        //     if (isset($payload['orderId'])) {
        //         try {
        //             $recipe = $this->kitchenService->selectRandomRecipe();
        //             $this->kitchenService->notifyOrderService($payload['orderId'], $recipe);

        //             $this->kitchenService->notifyStoreService($recipe);

        //             $this->info("Processed order ID: {$payload['orderId']}");
        //         } catch (\Exception $e) {
        //             $this->error("Error processing order: " . $e->getMessage());
        //         }
        //     } else {
        //         $this->error("Invalid message received: " . $message->getBody());
        //     }
        // });
    }
}
