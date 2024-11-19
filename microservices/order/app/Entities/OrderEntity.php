<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderEntity extends Model {
    protected $table = 'orders.orders';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['recipe_name', 'status'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
