<?php

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class PublishStrategy implements RabbitMQStrategy {

    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function getType(): string {
        return 'publish';
    }

    public function execute(array $params): void {
        $message = new AMQPMessage(
            json_encode($params['message']),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );
        $this->logger->info('Message created: ' . json_encode($params['message']));

        $params['channel']->queue_declare('kitchen_exchange', false, true, false, false);
        $this->logger->info('QUEUE "kitchen_exchange" ready for publish.');

        $params['channel']->basic_publish(
            $message,
            $params['exchange'],
            $params['routingKey']
        );
    }
}
