<?php

namespace Order\Providers;

use Order\Providers\Interfaces\IRabbitMQProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQProvider implements IRabbitMQProvider{
    private ?AMQPStreamConnection $connection = null;

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

    public function publish(string $exchange, string $routingKey, array $messageBody): void {
        try {
            $this->declareExchange($exchange, 'topic');

            $this->connect();
            $channel = $this->connection->channel();

            $message = new AMQPMessage(json_encode($messageBody), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($message, $exchange, $routingKey);
        } catch (Exception $e) {
            logger()->error('Error al publicar mensaje en RabbitMQ: ' . $e->getMessage());
            throw $e;
        }
    }

    public function consume(string $queue, callable $callback): void {
        try {
            $this->connect();

            $channel = $this->connection->channel();

            $channel->queue_declare($queue, false, true, false, false);
            $channel->basic_consume($queue, '', false, true, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
        } catch (Exception $e) {
            logger()->error('Error al consumir RabbitMQ: ' . $e->getMessage());
            throw $e;
        }
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

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
