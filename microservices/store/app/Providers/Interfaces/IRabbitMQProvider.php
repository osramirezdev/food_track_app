<?php

namespace Store\Providers\Interfaces;

interface IRabbitMQProvider {
    public function declareExchange(string $exchangeName, string $type = 'topic', bool $durable = true): void;
    public function declareQueueWithBindings(string $queueName, string $exchangeName, string $routingKey): void;
    public function executeStrategy(string $type, array $params): void;
    public function getChannel();
}
