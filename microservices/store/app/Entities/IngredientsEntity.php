<?php

namespace Store\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientsEntity extends Model {
    use HasFactory;
    protected $table = 'store.ingredients';
    protected $primaryKey = 'ingredient_name';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'ingredient_name',
        'current_stock',
    ];

    protected $casts = [
        'ingredient_name' => 'string',
        'current_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    protected $attributes = [
        'current_stock' => 5,
    ];
}
