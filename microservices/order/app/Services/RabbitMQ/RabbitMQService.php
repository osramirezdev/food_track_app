<?php

namespace App\Services\RabbitMQ;

interface RabbitMQService {
    public function publish(string $queue, string $message): void;
}
