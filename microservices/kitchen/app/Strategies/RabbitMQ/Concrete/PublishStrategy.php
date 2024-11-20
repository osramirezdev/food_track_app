<?php

namespace Kitchen\Strategies\RabbitMQ\Concrete;

use Kitchen\Strategies\RabbitMQ\RabbitMQStrategy;
use PhpAmqpLib\Message\AMQPMessage;

class PublishStrategy implements RabbitMQStrategy {

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

        $params['channel']->basic_publish(
            $message,
            $params['exchange'],
            $params['routingKey']
        );
    }
}
