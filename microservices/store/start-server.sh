#!/bin/bash

set -a
source .env
set +a

# iniciamos laravel
php artisan serve --host=0.0.0.0 --port=${MS_STORE_PORT:-8000} &

# ejecutamos en segundo plano el consumidor
php artisan rabbitmq:consume-store
