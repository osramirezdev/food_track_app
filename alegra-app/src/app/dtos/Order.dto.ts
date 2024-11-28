import { OrderStatusEnum, RecipeNameEnum } from "../enums";

export class OrderDTO {
    constructor(
        partial?: Partial<OrderDTO>
    ) {
        Object.assign(this, partial);
    }

    id?: number;
    recipe_name?: RecipeNameEnum;
    status?: OrderStatusEnum;
    createdAt?: string;
    updatedAt?: string;

}