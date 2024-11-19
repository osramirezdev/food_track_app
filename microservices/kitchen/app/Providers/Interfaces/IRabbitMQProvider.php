<?php

namespace App\Providers\Interfaces;

interface IRabbitMQProvider {
    public function publish(string $exchange, string $routingKey, array $message): void;
    public function consume(string $queue, callable $callback): void;
}
