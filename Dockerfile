FROM alpine:3.18

RUN apk add --no-cache docker-cli docker-compose

WORKDIR /usr/local/bin

COPY deploy.sh /usr/local/bin/deploy.sh
COPY rabbitmq/docker-compose.yml /rabbitmq/docker-compose.yml
COPY rabbitmq/.env /rabbitmq/.env

RUN chmod +x /usr/local/bin/deploy.sh

ENTRYPOINT ["/bin/sh", "/usr/local/bin/deploy.sh"]
