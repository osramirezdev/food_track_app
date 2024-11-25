<?php

namespace Order\Strategies\RabbitMQ\Concrete;

use Illuminate\Support\Facades\Log;
use Order\Strategies\RabbitMQ\RabbitMQStrategy;
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
        Log::channel('console')->debug("Publishing to kitchen ", ["data" => $params]);
        $message = new AMQPMessage(
            json_encode($params['message']),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );
        $this->logger->info('Publishing message: ' . json_encode($params['message']));
        Log::channel('console')->info('Testing spatie log'. json_encode($params['message']));

        $params['channel']->basic_publish(
            $message,
            $params['exchange'],
            $params['routingKey']
        );
        Log::channel('console')->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey'] . $params['message']);
        $this->logger->info('Message published to exchange: ' . $params['exchange'] . ', and routing key: ' . $params['routingKey']);

    }
}
