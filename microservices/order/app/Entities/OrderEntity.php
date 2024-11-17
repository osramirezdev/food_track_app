<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderEntity extends Model {
    protected $table = 'orders.orders';
    protected $fillable = ['recipe_name', 'status'];
    public $timestamps = true;
}
