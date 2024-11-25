# Microservicio `Store`

## Descripción

Este microservicio crea validar la disponibilidad de ingredientes y se comunica con otros microservicios mediante RabbitMQ.

## Inicialización

Para levantar este servicio individualmente, ejecuta:

```bash
cp .env.example .env
docker compose up --build -d
```

### Estructura de microservicio
- [Revisión general del directorio y archivos del microservicio Store](./docs/project-structure.md)
