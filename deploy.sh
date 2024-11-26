#!/bin/bash

MICROSERVICES_DIR="/app/microservices"

main() {
  echo "Starting microservices deployment..."

  for service in $(ls -1 "$MICROSERVICES_DIR"); do
    if [[ -d "$MICROSERVICES_DIR/$service" && -f "$MICROSERVICES_DIR/$service/docker-compose.yml" ]]; then
      echo "Service ready for manual deployment: $service"
    else
      echo "Skipping $service: Missing docker file or incorrect structure."
    fi
  done

  echo "Deployment process completed!"
}

main
