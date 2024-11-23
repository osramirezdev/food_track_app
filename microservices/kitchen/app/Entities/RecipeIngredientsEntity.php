<?php

namespace Kitchen\Entities;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredientsEntity extends Model {
    protected $table = 'kitchen.recipe_ingredients';
    public $timestamps = false;

    protected $fillable = [
        'recipe_name',
        'ingredient_name',
        'quantity_required'
    ];

    public function recipe() {
        return $this->belongsTo(RecipeEntity::class, 'recipe_name', 'name');
    }
}
