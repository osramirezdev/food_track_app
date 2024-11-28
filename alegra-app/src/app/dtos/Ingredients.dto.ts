import { OrderStatusEnum, RecipeNameEnum } from "../enums";

export class IngredientsDTO {
    constructor(
        partial?: Partial<IngredientsDTO>
    ) {
        Object.assign(this, partial);
    }

    ingredient_name?: RecipeNameEnum;
    current_stock?: number;
    createdAt?: string;
    updatedAt?: string;

}