#!/bin/bash

# es como un 'transactional' a nivel script shell?
set -euo pipefail

# FIXME: Por ahora se resume en este script, lo ideal seria llevarlo a su abstraccion correcta
# en Jenkins u otro.

# Nombre que le pondré a la red para los contenedores
NETWORK_NAME="backend_network"

#BASE_DIR=$(realpath $(dirname "$0")) # va retornar ruta absoluta 

# se reduce a esto, por que en el contexto de docker, los archivos estan en esta ruta
MICROSERVICES_DIR="/app/microservices"

# cuando se encuentra que no existe el comando en el contenedor
DOCKER_COMPOSE=$(command -v docker-compose || command -v docker compose)

# funcion para verificar si existe la red compartida con los contenedores
create_network() {
  if ! docker network ls | grep -q "$NETWORK_NAME"; then
    echo "Creating network: $NETWORK_NAME"
    docker network create "$NETWORK_NAME"
  else
    echo "Network $NETWORK_NAME already exists"
  fi
}

# inicializamos rabbit, con su respectivo archivo .env
start_rabbitmq() {
  echo "Starting RabbitMQ service..."  
  $DOCKER_COMPOSE --env-file /app/rabbitmq/.env -f /app/rabbitmq/docker-compose.yml up -d
}

# espearmos que rabbit este arriba
await_rabbit() {
  local timeout=60 # tiempo que vamos esperar
  local elapsed=0

  echo "Waiting for RabbitMQ to be ready..."
  while ! docker logs rabbitmq 2>&1 | grep -q "Server startup complete"; do # hacemos grep por que en docker logs retorna algo como `[info] <0.688.0> Server startup complete; 5 plugins started.`
    sleep 2
    elapsed=$((elapsed + 2)) # el buen ++ * 2
    if [ "$elapsed" -ge "$timeout" ]; then # parece un gran ORM, si el tiempo transcurrido es >= tiempo de espera
      echo "Error: Timed out waiting for RabbitMQ to be ready."
      exit 1
    fi
  done
  echo "RabbitMQ is ready!"
}

# y tambien esperar a docker, que se toma su tiempo
await_docker_backend_network() {
  local timeout=30
  local elapsed=0

  echo "Waiting for network $NETWORK_NAME to be ready..."
  while ! docker network inspect "$NETWORK_NAME" >/dev/null 2>&1; do
    sleep 1
    elapsed=$((elapsed + 1)) # el buen ++
    if [ "$elapsed" -ge "$timeout" ]; then
      echo "Error: Timed out waiting for network $NETWORK_NAME."
      echo "Attempting to create the network: $NETWORK_NAME..."
      docker network create "$NETWORK_NAME"
      if [ $? -ne 0 ]; then # si el ultimo comando ejecutado $0 no es igual a cero, quiere decir error
        echo "Error: Failed to create network $NETWORK_NAME."
        exit 1
      fi
      echo "Network $NETWORK_NAME successfully created."
      break
    fi
  done
  echo "Network $NETWORK_NAME is ready!"
}

# ahora vamos a iterar nuestra carpetas de microservicios para inicializarlos
# y evitar tener que hacerlo de a uno
start_microservices() {
  echo "Starting deployment of microservices..."
  for  service in $(ls -1 "$MICROSERVICES_DIR"); do
    if [[ -d "$MICROSERVICES_DIR/$service/docker" && -f "$MICROSERVICES_DIR/$service/docker/docker-compose.yml" ]]; then # verificamos que exista directorio y archivo
      echo "Deploying $service..."
      $DOCKER_COMPOSE -f "$MICROSERVICES_DIR/$service/docker/docker-compose.yml" up --build -d
    else
      echo "Skipping $service: Missing docker file or incorrect structure."
    fi
  done 
}

# hilo principal
main() {
  echo "Starting deploy process..."

  create_network

  await_docker_backend_network

  start_rabbitmq

  await_rabbit

  start_microservices

  echo "All services deployed!!!"
}


main

tail -f /dev/null
