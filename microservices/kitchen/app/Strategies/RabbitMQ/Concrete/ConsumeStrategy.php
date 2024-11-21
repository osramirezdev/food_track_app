<?php

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
use Psr\Log\LoggerInterface;

class ConsumeStrategy implements RabbitMQStrategy {

    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function getType(): string {
        return 'consume';
    }

    public function execute(array $params): void {

        $params['channel']->queue_declare('kitchen_exchange', false, true, false, false);
        $this->logger->info('QUEUE "kitchen_exchange" correctly declared.');

        $params['channel']->queue_bind('kitchen_exchange', 'order_exchange', 'order.kitchen');
        $this->logger->info('QUEUE "kitchen_exchange" binding "order_exchange" with routing "order.kitchen".');

        $params['channel']->basic_consume(
            $params['queue'],
            '',
            false,
            true,
            false,
            false,
            $params['callback']
        );

        $this->logger->info('Consumidor configurado, esperando mensajes.');

        while ($params['channel']->is_consuming()) {
            $params['channel']->wait();
        }
    }
}
