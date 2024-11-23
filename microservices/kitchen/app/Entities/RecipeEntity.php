<?php

namespace Kitchen\Entities;

use Illuminate\Database\Eloquent\Model;

class RecipeEntity extends Model {
    protected $table = 'kitchen.recipes';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function ingredients() {
        return $this->hasMany(RecipeIngredientsEntity::class, 'recipe_name', 'name');
    }
}
