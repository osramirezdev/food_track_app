<?php

namespace App\Factories;

use App\DTOs\OrderDTO;
use App\Enums\OrderStatusEnum;

class OrderDTOFactory {

    public static function createOrderDTO(array $parameters = []): OrderDTO {
        $parameters['status'] = $parameters['status'] ?? OrderStatusEnum::PENDIENTE->value;
        return OrderDTO::from($parameters);
    }
}