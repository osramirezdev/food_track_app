#!/bin/bash

php artisan serve --host=0.0.0.0 --port=${APP_PORT:-8000} &

LARAVEL_PID=$!

php artisan rabbitmq:consume-order &
php artisan rabbitmq:consume-store &

wait $LARAVEL_PID
