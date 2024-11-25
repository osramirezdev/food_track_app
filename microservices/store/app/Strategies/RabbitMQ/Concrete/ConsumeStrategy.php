<?php

namespace Store\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Store\Strategies\RabbitMQ\RabbitMQStrategy;
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
        Log::channel('console')->info('Init spatie log'. json_encode($params));

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
