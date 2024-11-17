FROM postgres:12

WORKDIR /app

COPY ./init.sh /docker-entrypoint-initdb.d/
COPY ./dump.sql ./scripts/db/dump.sql

RUN chmod +x /docker-entrypoint-initdb.d/init.sh
