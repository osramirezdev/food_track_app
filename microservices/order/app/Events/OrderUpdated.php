<?php

namespace Order\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;
use Order\DTOs\OrderDTO;

class OrderUpdated implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderDTO $order;

    /**
     * Create a new event instance.
     */
    public function __construct(OrderDTO $order) {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {
        Log::channel("console")->info('Broadcasting event on channel: AllOrders');
        return [
            new Channel('AllOrders'),
        ];
    }
    public function broadcastAs() {
        return 'OrderUpdated';
    }
    public function broadcastWith() {
        Log::info('Emitiendo datos del pedido:', [
            'orderId' => $this->order->orderId,
            'recipeName' => $this->order->recipeName,
            'status' => $this->order->status,
        ]);
        return [
            'orderId' => $this->order->orderId,
            'recipeName' => $this->order->recipeName,
            'status' => $this->order->status,
        ];
    }

}
