<?php

namespace Store\Factories;

use Store\Strategies\RabbitMQ\RabbitMQStrategy;
use InvalidArgumentException;

class RabbitMQStrategyFactory {
    private array $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = [];
        foreach ($strategies as $strategy) {
            if ($strategy instanceof RabbitMQStrategy) {
                $this->strategies[$strategy->getType()] = $strategy;
            }
        }
    }

    public function getStrategy(string $type): RabbitMQStrategy {
        if (!isset($this->strategies[$type])) {
            throw new InvalidArgumentException("No strategy found for type: {$type}");
        }
        return $this->strategies[$type];
    }
}
