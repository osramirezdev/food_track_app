# Microservicio `Kitchen`

## Descripción

Este microservicio gestiona las órdenes de cocina y se comunica con otros microservicios mediante RabbitMQ.

## Inicialización

Para levantar este servicio individualmente, ejecuta:

```bash
cp .env.example .env
docker compose up --build -d
```

### Estructura de microservicio
- [Revisión general del directorio y archivos del microservicio Kitcheb](./docs/project-structure.md)