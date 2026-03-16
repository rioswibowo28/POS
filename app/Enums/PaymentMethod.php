<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case EWALLET = 'ewallet';
    case VIRTUAL_ACCOUNT = 'virtual_account';
    case QRIS = 'qris';
    case CREDIT_CARD = 'credit_card';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::EWALLET => 'E-Wallet',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::QRIS => 'QRIS',
            self::CREDIT_CARD => 'Credit Card',
        };
    }

    public function needsGateway(): bool
    {
        return $this !== self::CASH;
    }
}
