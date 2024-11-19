<?php

namespace App\Enums;

enum OrderStatusEnum: string {
    case PENDIENTE = 'PENDIENTE';
    case PROCESANDO = 'PROCESANDO';
    case LISTO = 'LISTO';
}
