<?php

namespace Kitchen\Providers\Interfaces;

interface IRabbitMQKitchenProvider {
    public function executeStrategy(string $type, array $params): void;
    public function getChannel();
}
