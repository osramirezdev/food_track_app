<?php

namespace App\Providers;

use App\Providers\Interfaces\IRabbitMQProvider;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQProvider implements IRabbitMQProvider{
    private ?AMQPStreamConnection $connection = null;

    private function validateConfiguration(): void {
        $requiredKeys = ['host', 'port', 'username', 'password', 'queue'];
        foreach ($requiredKeys as $key) {
            if (empty(config("rabbitmq.$key"))) {
                throw new Exception("La configuración de RabbitMQ '$key' no está definida.");
            }
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
                throw $e;
            }
        }
    }

    public function publish(string $exchange, string $routingKey, array $messageBody): void {
        try {
            $this->connect();

            $channel = $this->connection->channel();

            $channel->exchange_declare(
                $exchange,
                'topic',
                false,
                true,
                false
            );

            $channel->queue_declare(
                $routingKey,
                false,
                true,
                false,
                false
            );

            $channel->queue_bind(
                $routingKey,
                $exchange,
                $routingKey
            );

            $message = new AMQPMessage(json_encode($messageBody), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($message, $exchange, $routingKey);

            $channel->close();
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

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
