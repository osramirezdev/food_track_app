#!/bin/bash

# iniciamos laravel
php artisan serve --host=0.0.0.0 --port=${APP_PORT:-8000} &

# ejecutamos en segundo plano el consumidor
php artisan rabbitmq:consume-orders