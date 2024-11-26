<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('OrderUpdate', function ($user) {
    return true;
});

Broadcast::channel('AllOrders', function ($user) {
    return true;
});
