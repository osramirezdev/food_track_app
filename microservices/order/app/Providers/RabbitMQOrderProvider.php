<?php

namespace Order\Providers;

use Order\Factories\RabbitMQStrategyFactory;
use Order\Providers\Interfaces\IRabbitMQProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQOrderProvider implements IRabbitMQProvider{
    private ?AMQPStreamConnection $connection = null;
    private RabbitMQStrategyFactory $strategyFactory;

    public function __construct(
        RabbitMQStrategyFactory $strategyFactory
    ) {
        $this->strategyFactory = $strategyFactory;
    }

    private function validateConfiguration(): void {
        $requiredKeys = ['host', 'port', 'username', 'password', 'queue'];
        foreach ($requiredKeys as $key) {
            if (empty(config("rabbitmq.$key"))) {
                throw new Exception("RabbitMQ '$key' not configured.");
            }
            Log::channel('console')->info("RabbitMQ '$key' defined.");
        }
    }

    private function connect(): void {
        if ($this->connection === null || !$this->connection->isConnected()) {
            try {
                $this->validateConfiguration();
                $this->connection = new AMQPStreamConnection(
                    config('rabbitmq.host'),
                    config('rabbitmq.port'),
                    config('rabbitmq.username'),
                    config('rabbitmq.password')
                );
            } catch (Exception $e) {
                logger()->error("Error connecting RabbitMQ: {$e->getMessage()}");
                Log::error("Error initializing rabbit ", ["error" => $e->getMessage()]);
                throw $e;
            }
        }
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

    public function executeStrategy(string $type, array $params): void {
        $this->connect();
        $channel = $this->connection->channel();

        $strategy = $this->strategyFactory->getStrategy($type);
        Log::debug("Se ha obtenido la estregia ", ["strategy" => $strategy]);

        $strategy->execute(array_merge(['channel' => $channel], $params));

        $channel->close();
    }

    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void {
        $this->connect();
        $channel = $this->getChannel();

        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchangeName, $routingKey);

        Log::info("Queue '{$queueName}' bound to exchange '{$exchangeName}' with routing key '{$routingKey}'");
    }

    public function getChannel(): AMQPChannel {
        $this->connect();
        return $this->connection->channel();
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
