<?php

namespace Store\Strategies\RabbitMQ;

interface RabbitMQStrategy {
    public function getType(): string;
    public function execute(array $params): void;
}
