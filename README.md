# prueba_tecnica_alegra_oscar_ramirez
Reto: 💥 Jornada de almuerzo ¡Gratis!

# Prueba Técnica Alegra: Sistema de Gestión de Pedidos de Cocina 🍽️

Este proyecto implementa un sistema de gestión de pedidos para un restaurante, basado en una **arquitectura de microservicios**. La aplicación permite realizar pedidos de platos aleatorios, consultar inventarios de ingredientes y gestionar la compra de ingredientes faltantes mediante una API externa.

## Arquitectura
El proyecto consta de los siguientes microservicios:
- **BFF (Backend for Frontend):** Interfaz que maneja las peticiones del cliente.
- **Cocina:** Selecciona aleatoriamente las recetas y prepara los platos solicitados.
- **Bodega:** Gestiona el inventario de ingredientes, reponiéndolos si es necesario.
- **Plaza:** Se conecta a una API externa para comprar ingredientes.

## Requisitos
- Docker y Docker Compose.
- PHP y Laravel (instalados en cada microservicio).

## Ejecución
Para levantar el entorno completo en Docker:
```bash
docker-compose up --build
