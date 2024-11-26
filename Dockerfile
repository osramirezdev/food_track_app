FROM alpine:3.18

RUN apk add --no-cache docker-cli docker-compose bash

WORKDIR /app

COPY deploy.sh /app/deploy.sh
COPY rabbitmq/docker-compose.yml /app/rabbitmq/docker-compose.yml
COPY rabbitmq/.env.example /app/rabbitmq/.env

COPY microservices /app/microservices

RUN chmod +x /app/deploy.sh

ENTRYPOINT ["/bin/sh", "/app/deploy.sh"]
