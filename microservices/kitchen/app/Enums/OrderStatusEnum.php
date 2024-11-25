<?php

namespace Kitchen\Enums;

enum OrderStatusEnum: string {
    case PENDIENTE = 'PENDIENTE';
    case ESPERANDO = 'ESPERANDO';
    case PROCESANDO = 'PROCESANDO';
    case LISTO = 'LISTO';
}
