<?php

namespace App\Enums;

enum StoreAvailabilityEnum: string {
    case AVAILABLE = 'Available Ingredients';
    case NOT_AVAILABLE = 'Not Available Ingredients';
}
