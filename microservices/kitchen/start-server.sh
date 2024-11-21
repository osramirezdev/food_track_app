#!/bin/bash

set -a
source .env
set +a

php artisan serve --host=0.0.0.0 --port=${MS_KITCHEN_PORT:-8000} &

LARAVEL_PID=$!

php artisan rabbitmq:consume-kitchen &

wait $LARAVEL_PID
