<?php

namespace Order\Factories;

use Order\DTOs\OrderDTO;
use Order\Enums\OrderStatusEnum;

class OrderDTOFactory {

    public static function createOrderDTO(array $parameters = []): OrderDTO {
        $parameters['status'] = $parameters['status'] ?? OrderStatusEnum::PENDIENTE->value;
        return OrderDTO::from($parameters);
    }
}