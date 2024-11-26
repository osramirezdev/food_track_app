FROM alpine:3.18

RUN apk add --no-cache docker-cli bash

WORKDIR /app

COPY deploy.sh /app/deploy.sh

RUN chmod +x /app/deploy.sh

ENTRYPOINT ["/bin/sh", "/app/deploy.sh"]