<?php

namespace App\Providers;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnection {
    private static ?RabbitMQConnection $instance = null;
    private AMQPStreamConnection $connection;

    private function __construct() {
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.username'),
            config('rabbitmq.password'),
        );
    }

    public static function getInstance(): RabbitMQConnection {
        if (self::$instance === null) {
            self::$instance = new RabbitMQConnection();
        }
        return self::$instance;
    }

    public function getChannel() {
        return $this->connection->channel();
    }

    public function close() {
        return $this->connection->close();
    }
}
