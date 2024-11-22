<?php

namespace Order\Mappers;

use Order\DTOs\OrderDTO;
use Order\Entities\OrderEntity;
use Order\Enums\OrderStatusEnum;
use Order\Enums\RecipeNameEnum;
use Order\Factories\OrderDTOFactory;

class OrderMapper {
    public static function dtoToEntity(OrderDTO $orderDTO): OrderEntity {
        $orderEntity = new OrderEntity();
        $orderEntity->recipe_name = $orderDTO->recipeName ? $orderDTO->recipeName->value : null;
        $orderEntity->status = $orderDTO->status->value;
        return $orderEntity;
    }

    public static function entityToDto(OrderEntity $orderEntity): OrderDTO {
        return OrderDTOFactory::createOrderDTO([
            'orderId' => $orderEntity->id ?? null,
            'recipeName' => $orderEntity->recipe_name ? RecipeNameEnum::from($orderEntity->recipe_name) : null,
            'status' => OrderStatusEnum::from($orderEntity->status ?? OrderStatusEnum::PENDIENTE),
            'createdAt' => $orderEntity->created_at ? $orderEntity->created_at->setTimezone('GMT-4') : null,
            'updatedAt' => $orderEntity->updated_at ? $orderEntity->updated_at->setTimezone('GMT-4') : null,
        ]);
    }

}
