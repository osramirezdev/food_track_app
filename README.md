# Prueba Técnica Alegra: Gestión Pedidos de kitchen 🍽️
### Reto: 💥 Jornada de almuerzo ¡Gratis!

Este proyecto implementa una aplicación de pedidos para un restaurante, utilizando una **arquitectura de microservicios**. La aplicación permite:
- Hacer pedidos de platos aleatorios.
- Consultar stock de ingredientes.
- Gestionar la compra de ingredientes faltantes.

## Arquitectura
El proyecto consta de los siguientes microservicios:
- **BFF (Backend for Frontend):** Interfaz que maneja las peticiones del cliente.
- **Kitchen:** Selecciona aleatoriamente las recetas y prepara los platos.
- **Store:** Gestiona el inventario de ingredientes, reponiéndolos si es necesario.
- **Mall:** Se conecta a una API externa para comprar ingredientes.

## Requisitos
- Docker y Docker Compose.
- PHP y Laravel.

## Diagrama de secuencia inicial
```mermaid
sequenceDiagram
    participant Cliente
    participant BFF as Backend for Frontend (BFF)
    participant Kitchen as Servicio de Kitchen
    participant RabbitMQ as RabbitMQ
    participant Store as Servicio de Store
    participant DB as PostgreSQL
    participant MercadoAPI as API Mall

    Cliente ->> BFF: Solicitar pedido de plato
    BFF ->> RabbitMQ: Publicar mensaje "Orden de Preparación de Plato"
    RabbitMQ ->> Kitchen: Consumir mensaje "Orden de Preparación de Plato"
    Kitchen ->> Kitchen: Seleccionar receta aleatoria
    Kitchen ->> RabbitMQ: Publicar mensaje "Solicitud de Ingredientes"
    RabbitMQ ->> Store: Consumir mensaje "Solicitud de Ingredientes"
    
    Store ->> DB: Verificar inventario
    alt Ingredientes Suficientes
        Store -->> Kitchen: Enviar ingredientes necesarios
    else Ingredientes Insuficientes
        Store ->> RabbitMQ: Publicar mensaje "Solicitud de Compra de Ingredientes"
        RabbitMQ ->> MercadoAPI: Enviar solicitud de compra de ingredientes a API de Plaza
        MercadoAPI -->> RabbitMQ: Responder con cantidad comprada
        RabbitMQ ->> Store: Notificación de compra exitosa
        Store ->> DB: Actualizar inventario con ingredientes comprados
        Store -->> Kitchen: Enviar ingredientes necesarios
    end

    Kitchen ->> DB: Marcar pedido como "En preparación"
    Kitchen ->> Kitchen: Preparar el plato
    Kitchen ->> DB: Marcar pedido como "Listo"
    Kitchen ->> RabbitMQ: Publicar mensaje "Plato Listo"
    RabbitMQ ->> BFF: Notificación de "Plato Listo"
    BFF -->> Cliente: Actualización del estado del pedido

```