<?php

namespace App\Enums;

enum OrderType: string
{
    case DINE_IN = 'dine_in';
    case TAKE_AWAY = 'take_away';
    case DELIVERY = 'delivery';

    public function label(): string
    {
        return match($this) {
            self::DINE_IN => 'Dine In',
            self::TAKE_AWAY => 'Take Away',
            self::DELIVERY => 'Delivery',
        };
    }
}
