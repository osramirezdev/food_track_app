import { OrderStatusEnum, RecipeNameEnum } from "../enums";

export class OrderDTO {
    constructor(
        partial?: Partial<OrderDTO>
    ) {
        Object.assign(this, partial);
    }

    orderId?: number;
    recipeName?: RecipeNameEnum;
    status?: OrderStatusEnum;
    createdAt?: string;
    updatedAt?: string;

}