<?php

namespace App\Services\RabbitMQ\Impl;

use App\Services\RabbitMQ\RabbitMQService;
use App\Providers\RabbitMQConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQServiceImpl implements RabbitMQService {
    public function publish(string $queue, string $message): void {
        $connection = RabbitMQConnection::getInstance();
        $channel = $connection->getChannel();

        $channel->queue_declare($queue, false, true, false, false);
        $channel->basic_publish(new AMQPMessage($message), '', $queue);

        $connection->close();
    }
}
