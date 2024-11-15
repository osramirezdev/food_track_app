#!/bin/bash

# FIXME: Por ahora se resume en este script, lo ideal seria llevarlo a su abstraccion correcta
# en Jenkins u otro.

# Nombre que le pondré a la red para los contenedores
NETWORK_NAME="backend_network"

create_network() {
  if ! docker network ls | grep -q "$NETWORK_NAME"; then
    echo "Creating network: $NETWORK_NAME"
    docker network create "$NETWORK_NAME"
  else
    echo "Network $NETWORK_NAME already exists"
  fi
}

start_rabbitmq() {
  echo "Starting RabbitMQ service..."
  docker-compose --env-file /rabbitmq/.env -f /rabbitmq/docker-compose.yml up -d
}

create_network
start_rabbitmq

# loop para mantener el contenedor en ejecución
tail -f /dev/null
