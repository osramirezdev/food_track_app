#!/bin/bash

MICROSERVICES_DIR="/app/microservices"

start_microservices() {
  echo "Starting deployment of microservices..."
  for  service in $(ls -1 "$MICROSERVICES_DIR"); do
    if [[ -d "$MICROSERVICES_DIR/$service" && -f "$MICROSERVICES_DIR/$service/docker-compose.yml" ]]; then # verificamos que exista directorio y archivo
      echo "Deploying $service... in $MICROSERVICES_DIR/$service/"
      (
        cd "$MICROSERVICES_DIR/$service" || exit
        $DOCKER_COMPOSE --env-file .env -f "$MICROSERVICES_DIR/$service/docker-compose.yml" up --build -d
      )
    else
      echo "Skipping $service: Missing docker file or incorrect structure."
    fi
  done 
}

main() {
  echo "Starting microservices deployment..."

  for service in $(ls -1 "$MICROSERVICES_DIR"); do
    if [[ -d "$MICROSERVICES_DIR/$service" && -f "$MICROSERVICES_DIR/$service/docker-compose.yml" ]]; then
      echo "Service ready for manual deployment: $service"
    else
      echo "Skipping $service: Missing docker file or incorrect structure."
    fi
  done


  start_microservices

  echo "Deployment process completed!"
}


main
