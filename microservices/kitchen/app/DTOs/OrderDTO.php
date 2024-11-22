<?php

namespace Kitchen\DTOs;

use Spatie\LaravelData\Data;

class OrderDTO extends Data {
    public ?int $orderId;
    public string $recipeName;
    public string $status;
}
