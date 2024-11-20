<?php

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;

class ConsumeStrategy implements RabbitMQStrategy {

    public function getType(): string {
        return 'consume';
    }

    public function execute(array $params): void {
        $params['channel']->basic_consume(
            $params['queue'],
            '',
            false,
            true,
            false,
            false,
            $params['callback']
        );

        while ($params['channel']->is_consuming()) {
            $params['channel']->wait();
        }
    }
}
