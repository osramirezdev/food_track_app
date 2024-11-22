<?php

namespace Order\Providers\Interfaces;

use PhpAmqpLib\Channel\AMQPChannel;

interface IRabbitMQProvider {
    public function publish(string $exchange, string $routingKey, array $message): void;
    public function consume(string $queue, callable $callback): void;
    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void;
    public function getChannel(): AMQPChannel;
}
