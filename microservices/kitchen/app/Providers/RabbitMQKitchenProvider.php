<?php

namespace Kitchen\Providers;

use Illuminate\Support\Facades\Log;
use Kitchen\Factories\RabbitMQStrategyFactory;
use Kitchen\Providers\Interfaces\IRabbitMQKitchenProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQKitchenProvider implements IRabbitMQKitchenProvider {
    private ?AMQPStreamConnection $connection = null;
    private RabbitMQStrategyFactory $strategyFactory;

    public function __construct(
        RabbitMQStrategyFactory $strategyFactory
    ) {
        $this->strategyFactory = $strategyFactory;
    }

    private function connect(): void {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.username'),
                config('rabbitmq.password')
            );
        }
    }

    public function executeStrategy(string $type, array $params): void {
        $this->connect();
        $channel = $this->connection->channel();

        $strategy = $this->strategyFactory->getStrategy($type);
        Log::debug("Se ha obtenido la estregia ", ["strategy" => $strategy]);

        $strategy->execute(array_merge(['channel' => $channel], $params));

        $channel->close();
    }

    public function getChannel(): AMQPChannel {
        $this->connect();
        return $this->connection->channel();
    }

    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void {
        $this->connect();
        $channel = $this->getChannel();
        Log::debug("Declaring Exchange. Channel: ", ["channel" => $channel]);
        $channel->exchange_declare(
            $exchangeName,
            $type,
            false,
            $durable,
            false
        );
    }
}
